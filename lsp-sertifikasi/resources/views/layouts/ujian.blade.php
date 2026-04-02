<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ujian') — LSP-KAP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            margin: 0;
            /* Cegah teks diseleksi selama ujian */
            -webkit-user-select: none;
            user-select: none;
        }

        /* ── Topbar ── */
        #ujian-topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            background: #1e3a5f;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }

        #ujian-topbar .brand {
            color: white;
            font-weight: 700;
            font-size: .95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #ujian-topbar .brand img {
            height: 28px;
            filter: brightness(0) invert(1);
            opacity: .85;
        }

        /* Timer box */
        #timer-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 8px;
            padding: 6px 14px;
        }

        #timer-box .label {
            font-size: .65rem;
            color: rgba(255,255,255,.6);
            font-weight: 700;
            letter-spacing: .08em;
            line-height: 1;
        }

        #timer-box #timerDisplay {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fbbf24;
            font-family: 'Courier New', monospace;
            line-height: 1;
            transition: color .3s;
        }

        #timer-box.danger #timerDisplay {
            color: #f87171;
            animation: timerPulse .8s ease-in-out infinite;
        }

        @keyframes timerPulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: .5; }
        }

        /* Progress pill */
        #progress-pill {
            font-size: .8rem;
            color: rgba(255,255,255,.8);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #progress-pill .pill-bar {
            width: 80px;
            height: 6px;
            background: rgba(255,255,255,.2);
            border-radius: 99px;
            overflow: hidden;
        }

        #progress-pill .pill-fill {
            height: 100%;
            background: #34d399;
            border-radius: 99px;
            transition: width .3s;
        }

        /* ── Main layout ── */
        #ujian-main {
            margin-top: 56px;
            display: flex;
            height: calc(100vh - 56px);
        }

        /* ── Nav panel (kiri) ── */
        #nav-panel {
            width: 220px;
            flex-shrink: 0;
            background: white;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
            padding: 16px 12px;
        }

        #nav-panel .nav-title {
            font-size: .68rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: .1em;
            text-transform: uppercase;
            margin-bottom: 10px;
            padding: 0 4px;
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 4px;
        }

        .nav-bubble {
            aspect-ratio: 1;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            background: white;
            font-size: .78rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all .15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-bubble:hover       { border-color: #94a3b8; background: #f8fafc; }
        .nav-bubble.answered    { background: #dcfce7; border-color: #86efac; color: #16a34a; }
        .nav-bubble.current     { background: #1e3a5f; border-color: #1e3a5f; color: white; }
        .nav-bubble.answered.current { background: #15803d; border-color: #15803d; color: white; }

        .nav-legend {
            margin-top: 12px;
            padding: 0 4px;
        }

        .nav-legend .leg-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .72rem;
            color: #64748b;
            margin-bottom: 4px;
        }

        .leg-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        /* ── Soal area ── */
        #soal-area {
            flex: 1;
            overflow-y: auto;
            padding: 24px 32px;
        }

        .soal-card {
            max-width: 760px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
            padding: 32px 36px;
        }

        .soal-number {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .soal-number .num-badge {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #1e3a5f;
            color: white;
            font-size: .9rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .soal-number .num-label {
            font-size: .8rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .soal-text {
            font-size: 1.05rem;
            font-weight: 500;
            line-height: 1.7;
            color: #1e293b;
            margin-bottom: 24px;
        }

        /* Pilihan jawaban */
        .pilihan-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            cursor: pointer;
            transition: all .15s;
            margin-bottom: 10px;
            user-select: none;
        }

        .pilihan-item:hover {
            border-color: #93c5fd;
            background: #eff6ff;
        }

        .pilihan-item.selected {
            border-color: #1e3a5f;
            background: #eff6ff;
        }

        .opt-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .9rem;
            color: #64748b;
            flex-shrink: 0;
            transition: all .15s;
        }

        .pilihan-item.selected .opt-circle {
            background: #1e3a5f;
            border-color: #1e3a5f;
            color: white;
        }

        .opt-text {
            font-size: .95rem;
            color: #334155;
            line-height: 1.5;
        }

        /* Nav bar bawah */
        .soal-nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        /* Saving indicator */
        #saving-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1e293b;
            color: white;
            font-size: .78rem;
            padding: 8px 14px;
            border-radius: 8px;
            display: none;
            align-items: center;
            gap: 6px;
            z-index: 999;
        }

        /* Block right-click & text selection during exam */
        #ujian-main { -webkit-user-select: none; user-select: none; }
    </style>

    @stack('styles')
</head>
<body>

{{-- Topbar --}}
<div id="ujian-topbar">
    <div class="brand">
        <i class="bi bi-shield-check" style="font-size:1.2rem;color:#60a5fa"></i>
        LSP-KAP &mdash; <span style="font-weight:400;opacity:.7">@yield('ujian-label', 'Ujian Soal Teori')</span>
    </div>

    <div class="d-flex align-items-center gap-3">
        {{-- Progress pill --}}
        <div id="progress-pill">
            <div class="pill-bar"><div class="pill-fill" id="topbarProgressFill" style="width:0%"></div></div>
            <span id="topbarProgressText">0 / 0</span>
        </div>

        {{-- Timer --}}
        <div id="timer-box">
            <div>
                <div class="label">WAKTU</div>
                <div id="timerDisplay">00:00:00</div>
            </div>
            <i class="bi bi-hourglass-split text-warning" style="font-size:1rem"></i>
        </div>

        {{-- Nama asesi --}}
        <div style="color:rgba(255,255,255,.7);font-size:.8rem;border-left:1px solid rgba(255,255,255,.15);padding-left:12px">
            @yield('ujian-asesi-name', '')
        </div>
    </div>
</div>

{{-- Main --}}
<div id="ujian-main">
    @yield('ujian-content')
</div>

{{-- Saving indicator --}}
<div id="saving-indicator">
    <span class="spinner-border spinner-border-sm"></span> Menyimpan...
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Blokir klik kanan --}}
<script>
    document.addEventListener('contextmenu', e => e.preventDefault());
</script>

@stack('scripts')
</body>
</html>