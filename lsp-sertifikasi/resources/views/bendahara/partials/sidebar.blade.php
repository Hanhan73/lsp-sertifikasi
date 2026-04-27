@php $route = Route::currentRouteName(); @endphp

<a href="{{ route('bendahara.dashboard') }}" class="nav-link {{ $route === 'bendahara.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

{{-- ── PEMBAYARAN ASESMEN ─────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>PEMBAYARAN ASESMEN</span>
</div>

<a href="{{ route('bendahara.payments.kolektif') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.payments.kolektif') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Kolektif
</a>

<a href="{{ route('bendahara.payments.index') }}"
    class="nav-link {{ $route === 'bendahara.payments.index' || $route === 'bendahara.payments.show' ? 'active' : '' }}">
    <i class="bi bi-person"></i> Individu
    @php $pendingCount = \App\Models\Payment::where('status','pending')->whereNotNull('proof_path')->count(); @endphp
    @if($pendingCount > 0)
    <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
    @endif
</a>

{{-- ── HONOR ──────────────────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>HONOR</span>
</div>

<a href="{{ route('bendahara.honor.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.honor') ? 'active' : '' }}">
    <i class="bi bi-person-badge"></i> Honor Asesmen Asesor
    @php
    $honorPending = \App\Models\HonorPayment::where('status','menunggu_pembayaran')->count();
    @endphp
    @if($honorPending > 0)
    <span class="badge bg-warning text-dark ms-1">{{ $honorPending }}</span>
    @endif
</a>

<a href="{{ route('bendahara.tarif-honor.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.tarif-honor') ? 'active' : '' }}">
    <i class="bi bi-sliders"></i> Tarif Honor Skema
</a>

<a href="{{ route('bendahara.rekap-pendapatan') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.rekap-pendapatan') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-line"></i> Rekap Pendapatan
</a>

{{-- ── BIAYA OPERASIONAL ──────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>BIAYA OPERASIONAL</span>
</div>

<a href="{{ route('bendahara.operasional.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.operasional') ? 'active' : '' }}">
    <i class="bi bi-receipt-cutoff"></i> Biaya Operasional
</a>

{{-- ── LAPORAN ─────────────────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>LAPORAN</span>
</div>

<a href="{{ route('bendahara.laporan.keuangan') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.laporan.keuangan') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-spreadsheet"></i> Laporan Keuangan
</a>

<a href="{{ route('bendahara.laporan.pajak') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.laporan.pajak') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Laporan Pajak
</a>

<a href="{{ route('bendahara.laporan.transaksi-harian') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.laporan.transaksi-harian') ? 'active' : '' }}">
    <i class="bi bi-calendar-day"></i> Transaksi Harian
</a>

<a href="{{ route('bendahara.laporan.buku-besar') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.laporan.buku-besar') ? 'active' : '' }}">
    <i class="bi bi-journal-bookmark"></i> Buku Besar
</a>