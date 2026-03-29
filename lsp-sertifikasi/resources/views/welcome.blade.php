<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LSP Sertifikasi — Sistem Sertifikasi Profesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
    /* ── Reset & Base ─────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }
    :root {
        --blue:       #2563eb;
        --blue-dark:  #1d4ed8;
        --blue-light: #eff6ff;
        --red:        #ef4444;
        --red-dark:   #dc2626;
        --green:      #10b981;
        --amber:      #f59e0b;
        --indigo:     #6366f1;
        --slate-900:  #0f172a;
        --slate-700:  #334155;
        --slate-500:  #64748b;
        --slate-200:  #e2e8f0;
        --slate-100:  #f1f5f9;
        --slate-50:   #f8fafc;
    }

    body {
        font-family: 'Inter', sans-serif;
        color: var(--slate-700);
        overflow-x: hidden;
    }

    /* ── Navbar ───────────────────────────────────────────── */
    .navbar {
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--slate-200);
        padding: .875rem 0;
        position: fixed; top: 0; width: 100%; z-index: 1000;
        transition: box-shadow .3s;
    }
    .navbar.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.08); }
    .navbar-brand img { height: 36px; }
    .navbar-brand span {
        font-weight: 800; font-size: 1.15rem; color: var(--slate-900);
    }

    .nav-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: .45rem 1.2rem; border-radius: 99px;
        font-weight: 600; font-size: .88rem;
        text-decoration: none; transition: all .22s;
        white-space: nowrap;
    }
    .nav-btn-outline {
        border: 1.5px solid var(--slate-200); color: var(--slate-700); background: #fff;
    }
    .nav-btn-outline:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-light); }
    .nav-btn-primary { background: var(--blue); color: #fff; border: none; box-shadow: 0 2px 10px rgba(37,99,235,.3); }
    .nav-btn-primary:hover { background: var(--blue-dark); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 18px rgba(37,99,235,.4); }
    .nav-btn-danger { background: var(--red); color: #fff; border: none; }
    .nav-btn-danger:hover { background: var(--red-dark); color: #fff; }

    /* ── Hero ─────────────────────────────────────────────── */
    .hero {
        min-height: 100vh;
        display: flex; align-items: center;
        background: linear-gradient(135deg, var(--blue-light) 0%, #fff 60%);
        padding: 100px 0 60px;
        position: relative; overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute; top: -120px; right: -120px;
        width: 520px; height: 520px;
        background: radial-gradient(circle, rgba(37,99,235,.08) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulse 6s ease-in-out infinite;
    }
    .hero::after {
        content: '';
        position: absolute; bottom: -80px; left: -80px;
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(239,68,68,.05) 0%, transparent 70%);
        border-radius: 50%;
    }
    @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }

    .hero-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--blue-light); color: var(--blue);
        border: 1px solid rgba(37,99,235,.2); border-radius: 99px;
        padding: 5px 14px; font-size: .78rem; font-weight: 600;
        margin-bottom: 20px;
    }
    .hero h1 {
        font-size: clamp(2rem, 5vw, 3.4rem);
        font-weight: 900; line-height: 1.15;
        color: var(--slate-900); margin-bottom: 1.25rem;
    }
    .hero h1 .c-blue  { color: var(--blue); }
    .hero h1 .c-red   { color: var(--red); }
    .hero p.lead {
        font-size: 1.05rem; color: var(--slate-500);
        line-height: 1.8; margin-bottom: 2rem; max-width: 480px;
    }
    .hero-img {
        max-width: 100%; border-radius: 20px;
        filter: drop-shadow(0 20px 40px rgba(37,99,235,.15));
        animation: float 4s ease-in-out infinite;
    }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-16px)} }

    .btn-hero-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--blue); color: #fff;
        padding: .85rem 2rem; border-radius: 99px;
        font-weight: 700; font-size: 1rem;
        text-decoration: none; transition: all .22s;
        box-shadow: 0 4px 18px rgba(37,99,235,.35);
    }
    .btn-hero-primary:hover { background: var(--blue-dark); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 28px rgba(37,99,235,.45); }
    .btn-hero-secondary {
        display: inline-flex; align-items: center; gap: 8px;
        background: #fff; color: var(--blue);
        padding: .85rem 2rem; border-radius: 99px;
        font-weight: 700; font-size: 1rem;
        border: 2px solid var(--blue);
        text-decoration: none; transition: all .22s;
    }
    .btn-hero-secondary:hover { background: var(--blue-light); color: var(--blue); }

    /* Logged-in greeting chip */
    .greeting-chip {
        display: inline-flex; align-items: center; gap: 8px;
        background: #fff; border: 1.5px solid var(--slate-200);
        border-radius: 99px; padding: 6px 16px 6px 8px;
        font-size: .82rem; color: var(--slate-700);
        margin-bottom: 18px;
    }
    .greeting-chip .av {
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--blue); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .75rem;
    }

    /* ── Stats Bar ────────────────────────────────────────── */
    .stats-bar {
        background: linear-gradient(135deg, var(--blue) 0%, var(--indigo) 100%);
        padding: 40px 0; color: #fff;
    }
    .stat-item { text-align: center; padding: 0 10px; }
    .stat-num  { font-size: 2.4rem; font-weight: 900; line-height: 1; }
    .stat-lbl  { font-size: .82rem; opacity: .8; margin-top: 4px; }

    /* ── How It Works ─────────────────────────────────────── */
    .section { padding: 80px 0; }
    .section-alt { background: var(--slate-50); }
    .section-head { text-align: center; margin-bottom: 48px; }
    .section-head .eyebrow {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--blue-light); color: var(--blue);
        border-radius: 99px; padding: 4px 14px;
        font-size: .75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em;
        margin-bottom: 12px;
    }
    .section-head h2 {
        font-size: clamp(1.6rem, 3.5vw, 2.4rem);
        font-weight: 800; color: var(--slate-900);
        margin-bottom: .5rem;
    }
    .section-head p { color: var(--slate-500); font-size: 1rem; max-width: 520px; margin: 0 auto; }
    .underline-accent {
        width: 60px; height: 4px; background: var(--red);
        border-radius: 2px; margin: 10px auto 0;
    }

    /* ── Step Card ────────────────────────────────────────── */
    .step-card {
        background: #fff; border-radius: 18px;
        border: 1.5px solid var(--slate-200);
        padding: 28px 24px; height: 100%;
        transition: all .25s; position: relative;
    }
    .step-card:hover { border-color: var(--blue); box-shadow: 0 8px 30px rgba(37,99,235,.1); transform: translateY(-4px); }
    .step-num {
        width: 44px; height: 44px; border-radius: 12px;
        background: var(--red); color: #fff;
        font-weight: 800; font-size: 1.1rem;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
    }
    .step-card h4 { font-weight: 700; font-size: 1.05rem; color: var(--slate-900); margin-bottom: 8px; }
    .step-card p  { color: var(--slate-500); font-size: .875rem; line-height: 1.6; margin-bottom: 0; }
    .step-card .step-action { margin-top: 16px; }

    /* ── Feature Card ─────────────────────────────────────── */
    .feat-card {
        background: #fff; border-radius: 18px;
        border: 1.5px solid var(--slate-200);
        padding: 28px; height: 100%;
        transition: all .25s; text-align: center;
    }
    .feat-card:hover { border-color: var(--blue); box-shadow: 0 8px 30px rgba(37,99,235,.1); transform: translateY(-4px); }
    .feat-icon {
        width: 64px; height: 64px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; margin: 0 auto 18px;
    }
    .feat-card h4 { font-weight: 700; font-size: 1rem; color: var(--slate-900); margin-bottom: 8px; }
    .feat-card p  { color: var(--slate-500); font-size: .85rem; line-height: 1.65; margin: 0; }

    /* ── Role Card ────────────────────────────────────────── */
    .role-card {
        background: #fff; border-radius: 20px;
        border: 2px solid var(--slate-200);
        padding: 32px; height: 100%;
        transition: all .25s;
    }
    .role-card:hover { border-color: var(--blue); box-shadow: 0 12px 40px rgba(37,99,235,.12); transform: translateY(-5px); }
    .role-icon {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; margin: 0 auto 18px;
        transition: all .3s;
    }
    .role-card:hover .role-icon { background: var(--blue) !important; color: #fff !important; }
    .role-card h3 { font-weight: 700; font-size: 1.2rem; color: var(--slate-900); text-align: center; margin-bottom: 16px; }
    .role-card ul { list-style: none; padding: 0; margin: 0; }
    .role-card ul li {
        padding: 6px 0 6px 22px; color: var(--slate-500);
        font-size: .875rem; position: relative;
    }
    .role-card ul li::before {
        content: '\F26A'; font-family: 'bootstrap-icons';
        position: absolute; left: 0; color: var(--blue);
    }

    /* ── CTA Banner ───────────────────────────────────────── */
    .cta-banner {
        background: linear-gradient(135deg, var(--blue) 0%, var(--indigo) 100%);
        border-radius: 24px; padding: 56px 48px;
        position: relative; overflow: hidden;
        color: #fff;
    }
    .cta-banner::before {
        content: '';
        position: absolute; top: -60px; right: -60px;
        width: 300px; height: 300px;
        background: rgba(255,255,255,.06); border-radius: 50%;
    }
    .cta-banner h2 { font-weight: 800; font-size: clamp(1.5rem, 3vw, 2.2rem); margin-bottom: .75rem; }
    .cta-banner p  { opacity: .85; font-size: 1rem; margin-bottom: 0; }

    .btn-cta-white {
        display: inline-flex; align-items: center; gap: 8px;
        background: #fff; color: var(--blue);
        padding: .85rem 2rem; border-radius: 99px;
        font-weight: 700; font-size: .95rem;
        text-decoration: none; transition: all .22s;
        box-shadow: 0 4px 16px rgba(0,0,0,.15);
    }
    .btn-cta-white:hover { background: var(--blue-light); color: var(--blue-dark); transform: translateY(-2px); }
    .btn-cta-outline {
        display: inline-flex; align-items: center; gap: 8px;
        background: transparent; color: #fff;
        padding: .85rem 2rem; border-radius: 99px;
        font-weight: 700; font-size: .95rem;
        border: 2px solid rgba(255,255,255,.5);
        text-decoration: none; transition: all .22s;
    }
    .btn-cta-outline:hover { border-color: #fff; background: rgba(255,255,255,.1); color: #fff; }

    /* ── Footer ───────────────────────────────────────────── */
    footer {
        background: var(--slate-900); color: rgba(255,255,255,.7);
        padding: 56px 0 28px;
    }
    footer h5 { color: #fff; font-weight: 700; margin-bottom: 18px; font-size: .95rem; }
    footer ul { list-style: none; padding: 0; }
    footer ul li { margin-bottom: 8px; }
    footer a { color: rgba(255,255,255,.6); text-decoration: none; font-size: .88rem; transition: color .2s; }
    footer a:hover { color: #fff; }
    .footer-brand img { height: 32px; filter: brightness(0) invert(1); margin-bottom: 12px; }
    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,.08);
        margin-top: 40px; padding-top: 24px;
        text-align: center; font-size: .8rem;
        color: rgba(255,255,255,.4);
    }
    .social-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 50%;
        background: rgba(255,255,255,.08); color: rgba(255,255,255,.6);
        text-decoration: none; transition: all .2s; margin-right: 8px;
    }
    .social-btn:hover { background: var(--blue); color: #fff; }

    /* ── Utility ──────────────────────────────────────────── */
    .text-blue { color: var(--blue) !important; }
    .text-red  { color: var(--red) !important; }
    .bg-blue-light { background: var(--blue-light) !important; }

    /* ── Responsive ───────────────────────────────────────── */
    @media(max-width:768px){
        .hero { padding: 90px 0 48px; text-align: center; }
        .hero p.lead, .hero h1 { max-width: 100%; }
        .hero-img { margin-top: 32px; }
        .cta-banner { padding: 36px 24px; }
        .stat-num { font-size: 1.8rem; }
    }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════
     NAVBAR
══════════════════════════════════════ --}}
<nav class="navbar" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="{{ route('home') }}">
            <img src="{{ asset('images/logo-lsp.png') }}" alt="LSP Logo">
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0 ms-lg-auto">
                @auth
                <span class="text-muted small me-1 d-none d-lg-inline">
                    <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                </span>
                @if(Auth::user()->isAdmin())
                <a class="nav-btn nav-btn-primary" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard Admin
                </a>
                @elseif(Auth::user()->isTuk())
                <a class="nav-btn nav-btn-primary" href="{{ route('tuk.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard TUK
                </a>
                @elseif(Auth::user()->isAsesi())
                <a class="nav-btn nav-btn-primary" href="{{ route('asesi.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard Saya
                </a>
                @elseif(Auth::user()->isAsesor())
                <a class="nav-btn nav-btn-primary" href="{{ route('asesor.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard Asesor
                </a>
                @elseif(Auth::user()->isDirektur())
                <a class="nav-btn nav-btn-primary" href="{{ route('direktur.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard Direktur
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="nav-btn nav-btn-outline border-0 bg-transparent cursor-pointer">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </button>
                </form>
                @else
                <a class="nav-btn nav-btn-outline" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </a>
                <a class="nav-btn nav-btn-primary" href="{{ route('register') }}">
                    <i class="bi bi-person-plus"></i> Daftar
                </a>
                @endauth
            </div>
        </div>
    </div>
</nav>


{{-- ══════════════════════════════════════
     HERO
══════════════════════════════════════ --}}
<section class="hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6">
                @auth
                {{-- Greeting chip untuk user yang sudah login --}}
                <div class="greeting-chip mb-2">
                    <div class="av">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <span>Selamat datang kembali, <strong>{{ Str::words(Auth::user()->name, 1, '') }}</strong>!</span>
                </div>

                @if(Auth::user()->isAdmin())
                <h1>Kelola Sistem <span class="c-blue">Sertifikasi</span> dengan <span class="c-red">Efisien</span></h1>
                <p class="lead">Pantau seluruh aktivitas, verifikasi data asesi, kelola TUK, asesor, dan skema sertifikasi dalam satu panel terpadu.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="btn-hero-primary">
                        <i class="bi bi-speedometer2"></i> Buka Dashboard
                    </a>
                    <a href="{{ route('admin.praasesmen.index') }}" class="btn-hero-secondary">
                        <i class="bi bi-list-check"></i> Pra-Asesmen
                    </a>
                </div>

                @elseif(Auth::user()->isTuk())
                <h1>Portal <span class="c-blue">TUK</span> — Kelola Asesi Anda</h1>
                <p class="lead">Daftarkan asesi secara kolektif, jadwalkan asesmen, dan pantau proses sertifikasi dari satu tempat.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('tuk.dashboard') }}" class="btn-hero-primary">
                        <i class="bi bi-speedometer2"></i> Buka Dashboard
                    </a>
                    <a href="{{ route('tuk.collective') }}" class="btn-hero-secondary">
                        <i class="bi bi-people"></i> Daftar Kolektif
                    </a>
                </div>

                @elseif(Auth::user()->isAsesi())
                <h1>Pantau Perjalanan <span class="c-blue">Sertifikasi</span> Anda</h1>
                <p class="lead">Lihat status pendaftaran, isi dokumen yang diperlukan, dan unduh sertifikat digital Anda.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('asesi.dashboard') }}" class="btn-hero-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard Saya
                    </a>
                </div>

                @elseif(Auth::user()->isAsesor())
                <h1>Portal <span class="c-blue">Asesor</span> Profesional</h1>
                <p class="lead">Lihat jadwal asesmen Anda, pantau peserta yang ditugaskan, dan kelola dokumen penilaian.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('asesor.dashboard') }}" class="btn-hero-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard Saya
                    </a>
                    <a href="{{ route('asesor.schedule') }}" class="btn-hero-secondary">
                        <i class="bi bi-calendar3"></i> Jadwal Saya
                    </a>
                </div>

                @elseif(Auth::user()->isDirektur())
                <div class="hero-badge"><i class="bi bi-shield-check"></i> Direktur LSP</div>
                <h1>Rekap & <span class="c-blue">Pengawasan</span> Sistem <span class="c-red">Sertifikasi</span></h1>
                <p class="lead">Lihat ringkasan aktivitas seluruh sistem, setujui jadwal asesmen, pantau progres asesi, asesor, dan skema.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('direktur.dashboard') }}" class="btn-hero-primary">
                        <i class="bi bi-speedometer2"></i> Buka Dashboard
                    </a>
                    <a href="{{ route('direktur.schedules.index') }}" class="btn-hero-secondary">
                        @php $pending = \App\Models\Schedule::pendingApproval()->count(); @endphp
                        <i class="bi bi-calendar-check"></i> Approval Jadwal
                        @if($pending > 0)
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem;">{{ $pending }}</span>
                        @endif
                    </a>
                </div>

                @endif

                @else
                {{-- Guest --}}
                <div class="hero-badge"><i class="bi bi-patch-check-fill"></i> Sistem Sertifikasi Profesi Resmi</div>
                <h1>Sertifikasi <span class="c-blue">Kompetensi</span> Kini Lebih <span class="c-red">Mudah</span></h1>
                <p class="lead">Platform digital terintegrasi untuk manajemen sertifikasi profesi — dari pendaftaran, asesmen, hingga penerbitan sertifikat dalam satu sistem.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        Daftar Sekarang <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ route('login') }}" class="btn-hero-secondary">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
                    </a>
                </div>
                @endauth
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0 text-center">
                <img src="https://img.freepik.com/free-vector/online-certification-illustration_23-2148575636.jpg"
                     alt="Sertifikasi Ilustrasi" class="hero-img">
            </div>
        </div>
    </div>
</section>



{{-- ══════════════════════════════════════
     CARA KERJA — Conditional per Role
══════════════════════════════════════ --}}
<section class="section section-alt" id="cara-kerja">
    <div class="container">
        @auth

        @if(Auth::user()->isAdmin())
        <div class="section-head">
            <div class="eyebrow"><i class="bi bi-gear-fill"></i> Admin</div>
            <h2>Tugas Administrator</h2>
            <div class="underline-accent"></div>
        </div>
        <div class="row g-4">
            @foreach([
                ['1','Kelola TUK & Skema','Tambah dan kelola data TUK serta skema sertifikasi yang aktif','bi-building','admin.tuks','Kelola TUK'],
                ['2','Verifikasi Asesi','Verifikasi data asesi yang masuk dan mulai proses asesmen','bi-list-check','admin.praasesmen.index','Pra-Asesmen'],
                ['3','Monitor Pembayaran','Pantau dan verifikasi pembayaran asesi mandiri','bi-cash-coin','admin.payments','Pembayaran'],
                ['4','Input Hasil & Laporan','Input hasil asesmen dan export laporan sistem','bi-file-earmark-bar-graph','admin.assessments','Asesmen'],
            ] as [$n, $title, $desc, $icon, $route, $label])
            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-num">{{ $n }}</div>
                    <h4>{{ $title }}</h4>
                    <p>{{ $desc }}</p>
                    <div class="step-action">
                        <a href="{{ route($route) }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @elseif(Auth::user()->isTuk())
        <div class="section-head">
            <div class="eyebrow"><i class="bi bi-building"></i> TUK</div>
            <h2>Tugas TUK Anda</h2>
            <div class="underline-accent"></div>
        </div>
        <div class="row g-4">
            @foreach([
                ['1','Daftar Kolektif','Daftarkan asesi secara massal dengan Batch ID','bi-people-fill','tuk.collective','Daftar Kolektif'],
                ['2','Verifikasi Data','Verifikasi kelengkapan data dan dokumen asesi','bi-clipboard-check','tuk.verifications.index','Verifikasi'],
                ['3','Jadwalkan Asesmen','Buat jadwal asesmen untuk asesi yang sudah siap','bi-calendar-plus','tuk.schedules','Buat Jadwal'],
                ['4','Pantau Pembayaran','Monitor status pembayaran asesi kolektif','bi-wallet2','tuk.collective.payments','Pembayaran'],
            ] as [$n, $title, $desc, $icon, $route, $label])
            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-num">{{ $n }}</div>
                    <h4>{{ $title }}</h4>
                    <p>{{ $desc }}</p>
                    <div class="step-action">
                        <a href="{{ route($route) }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @elseif(Auth::user()->isAsesi())
        <div class="section-head">
            <div class="eyebrow"><i class="bi bi-person-check"></i> Asesi</div>
            <h2>Perjalanan Sertifikasi Anda</h2>
            <div class="underline-accent"></div>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['1','Daftar & Isi Data','Lengkapi biodata dan unggah dokumen persyaratan','bi-person-plus'],
                ['2','Tunggu Verifikasi','Admin LSP memverifikasi data dan memulai asesmen','bi-hourglass-split'],
                ['3','Isi Dokumen Asesmen','Isi APL-01, APL-02, dan FR.AK.01 secara digital','bi-pencil-square'],
                ['4','Ikuti Jadwal Asesmen','Hadiri asesmen sesuai jadwal yang ditentukan TUK','bi-calendar-event'],
                ['5','Terima Sertifikat','Download sertifikat digital jika dinyatakan kompeten','bi-award'],
            ] as [$n, $title, $desc, $icon])
            <div class="col-lg col-md-4 col-sm-6">
                <div class="step-card text-center">
                    <div class="step-num mx-auto">{{ $n }}</div>
                    <i class="bi {{ $icon }} text-blue" style="font-size:1.5rem;"></i>
                    <h4 class="mt-2">{{ $title }}</h4>
                    <p>{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('asesi.dashboard') }}" class="btn-hero-primary">
                <i class="bi bi-speedometer2"></i> Lihat Status Saya
            </a>
        </div>

        @elseif(Auth::user()->isAsesor())
        <div class="section-head">
            <div class="eyebrow"><i class="bi bi-person-badge"></i> Asesor</div>
            <h2>Tugas Asesor</h2>
            <div class="underline-accent"></div>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['1','Terima Penugasan','Menerima notifikasi jadwal dari admin LSP','bi-bell-fill','asesor.dashboard','Dashboard'],
                ['2','Review Dokumen','Periksa APL-01 dan APL-02 asesi sebelum asesmen','bi-file-earmark-text','asesor.schedule','Jadwal Saya'],
                ['3','Lakukan Asesmen','Isi checklist FR.AK.01 dan tandatangani dokumen','bi-check2-all','asesor.schedule','Mulai Asesmen'],
            ] as [$n, $title, $desc, $icon, $route, $label])
            <div class="col-lg-4 col-md-6">
                <div class="step-card">
                    <div class="step-num">{{ $n }}</div>
                    <h4>{{ $title }}</h4>
                    <p>{{ $desc }}</p>
                    <div class="step-action">
                        <a href="{{ route($route) }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @elseif(Auth::user()->isDirektur())
        <div class="section-head">
            <div class="eyebrow"><i class="bi bi-shield-check"></i> Direktur</div>
            <h2>Kewenangan Direktur</h2>
            <div class="underline-accent"></div>
        </div>
        <div class="row g-4">
            @foreach([
                ['1','Pantau Rekap Sistem','Lihat total asesi, progres per tahap, dan statistik keseluruhan sistem','bi-bar-chart-line-fill','direktur.dashboard','Buka Dashboard'],
                ['2','Pantau TUK & Batch','Rekap jumlah asesi per TUK, termasuk pendaftaran kolektif per batch','bi-building','direktur.dashboard','Lihat TUK'],
                ['3','Data Asesor','Lihat asesor aktif, skema yang dikuasai, dan jumlah jadwal yang dimiliki','bi-person-badge','direktur.dashboard','Lihat Asesor'],
                ['4','Approval Jadwal','Review dan setujui jadwal asesmen. SK otomatis ter-generate setelah disetujui','bi-calendar-check-fill','direktur.schedules.index','Review Jadwal'],
            ] as [$n, $title, $desc, $icon, $route, $label])
            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-num">{{ $n }}</div>
                    <h4>{{ $title }}</h4>
                    <p>{{ $desc }}</p>
                    <div class="step-action">
                        <a href="{{ route($route) }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @endif

        @else
        {{-- Guest: cara kerja umum --}}
        <div class="section-head">
            <div class="eyebrow"><i class="bi bi-map"></i> Cara Kerja</div>
            <h2>Proses Sertifikasi dalam 4 Langkah</h2>
            <div class="underline-accent"></div>
        </div>
        <div class="row g-4">
            @foreach([
                ['1','Daftar Akun','Buat akun dan lengkapi biodata. Bisa mandiri atau didaftarkan kolektif oleh TUK','bi-person-plus'],
                ['2','Pilih Skema','Pilih skema sertifikasi sesuai kompetensi yang ingin diakui','bi-patch-check'],
                ['3','Ikuti Asesmen','Isi dokumen, hadir di jadwal asesmen, dan jalani penilaian bersama asesor','bi-pencil-square'],
                ['4','Dapatkan Sertifikat','Unduh sertifikat digital kompetensi Anda yang sah dan terverifikasi','bi-award'],
            ] as [$n, $title, $desc, $icon])
            <div class="col-lg-3 col-md-6">
                <div class="step-card">
                    <div class="step-num">{{ $n }}</div>
                    <i class="bi {{ $icon }} text-blue mb-2 d-block" style="font-size:1.4rem;"></i>
                    <h4>{{ $title }}</h4>
                    <p>{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endauth
    </div>
</section>


{{-- ══════════════════════════════════════
     PERAN PENGGUNA (hanya untuk tamu)
══════════════════════════════════════ --}}
@guest
<section class="section" id="peran">
    <div class="container">
        <div class="section-head">
            <div class="eyebrow"><i class="bi bi-people-fill"></i> Peran</div>
            <h2>Untuk Siapa Platform Ini?</h2>
            <div class="underline-accent"></div>
            <p class="mt-3">Dirancang untuk semua pemangku kepentingan dalam ekosistem sertifikasi profesi</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="role-card">
                    <div class="role-icon" style="background:var(--blue-light);color:var(--blue);">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3>Asesi</h3>
                    <ul>
                        <li>Pendaftaran online mandiri</li>
                        <li>Upload dokumen persyaratan</li>
                        <li>Pembayaran digital</li>
                        <li>Tracking status real-time</li>
                        <li>Isi dokumen APL & FR digital</li>
                        <li>Download sertifikat digital</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="role-card">
                    <div class="role-icon" style="background:#f0fdf4;color:var(--green);">
                        <i class="bi bi-building"></i>
                    </div>
                    <h3>TUK</h3>
                    <ul>
                        <li>Pendaftaran kolektif (batch)</li>
                        <li>Verifikasi data asesi</li>
                        <li>Kelola pembayaran kolektif</li>
                        <li>Buat jadwal asesmen</li>
                        <li>Pantau progres asesi</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="role-card">
                    <div class="role-icon" style="background:#fdf4ff;color:var(--indigo);">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h3>Asesor</h3>
                    <ul>
                        <li>Terima notifikasi penugasan</li>
                        <li>Akses jadwal & peserta</li>
                        <li>Review dokumen APL</li>
                        <li>Isi & tandatangani FR.AK.01</li>
                        <li>Akses SK digital</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="role-card">
                    <div class="role-icon" style="background:#fefce8;color:var(--amber);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Direktur</h3>
                    <ul>
                        <li>Dashboard rekap sistem</li>
                        <li>Pantau progres asesi</li>
                        <li>Monitor TUK & batch</li>
                        <li>Pantau data asesor</li>
                        <li>Approval jadwal asesmen</li>
                        <li>Generate SK otomatis</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endguest


{{-- ══════════════════════════════════════
     CTA
══════════════════════════════════════ --}}
<section class="section section-alt">
    <div class="container">
        <div class="cta-banner">
            @auth
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2>Siap melanjutkan?</h2>
                    <p>Masuk ke dashboard Anda dan lanjutkan aktivitas sertifikasi.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex flex-wrap gap-3 justify-content-lg-end">
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-cta-white"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
                    @elseif(Auth::user()->isTuk())
                    <a href="{{ route('tuk.dashboard') }}" class="btn-cta-white"><i class="bi bi-speedometer2"></i> Dashboard TUK</a>
                    @elseif(Auth::user()->isAsesi())
                    <a href="{{ route('asesi.dashboard') }}" class="btn-cta-white"><i class="bi bi-speedometer2"></i> Dashboard Saya</a>
                    @elseif(Auth::user()->isAsesor())
                    <a href="{{ route('asesor.dashboard') }}" class="btn-cta-white"><i class="bi bi-speedometer2"></i> Dashboard Asesor</a>
                    @elseif(Auth::user()->isDirektur())
                    <a href="{{ route('direktur.dashboard') }}" class="btn-cta-white"><i class="bi bi-speedometer2"></i> Dashboard Direktur</a>
                    @endif
                </div>
            </div>
            @else
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2>Siap Memulai Sertifikasi?</h2>
                    <p>Bergabung dengan ribuan profesional yang telah tersertifikasi melalui platform kami.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex flex-wrap gap-3 justify-content-lg-end">
                    <a href="{{ route('register') }}" class="btn-cta-white">
                        <i class="bi bi-person-plus"></i> Daftar Sekarang
                    </a>
                    <a href="{{ route('login') }}" class="btn-cta-outline">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </a>
                </div>
            </div>
            @endauth
        </div>
    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Navbar shadow on scroll
window.addEventListener('scroll', () => {
    document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 20);
});

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
</body>
</html>