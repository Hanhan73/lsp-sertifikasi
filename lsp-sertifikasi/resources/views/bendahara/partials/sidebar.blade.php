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
        $honorMenunggu = \App\Models\HonorPayment::where('status', 'menunggu_pembayaran')->count();

        $scheduleIdsSudahKwitansi = \App\Models\HonorPaymentDetail::whereHas('honorPayment', function ($q) {
            $q->whereIn('status', ['menunggu_pembayaran', 'sudah_dibayar', 'dikonfirmasi']);
        })->pluck('schedule_id');

        $jadwalBelumDibuat = \App\Models\Schedule::whereHas('beritaAcara')
            ->whereNotIn('id', $scheduleIdsSudahKwitansi)
            ->count();
    @endphp
    @if($honorMenunggu > 0)
    <span class="badge bg-danger ms-1" title="{{ $honorMenunggu }} kwitansi belum dibayar">
        {{ $honorMenunggu }}
    </span>
    @endif
    @if($jadwalBelumDibuat > 0)
    <span class="badge bg-secondary ms-1" title="{{ $jadwalBelumDibuat }} jadwal belum dibuat kwitansi">
        {{ $jadwalBelumDibuat }}
    </span>
    @endif
</a>

<a href="{{ route('bendahara.tarif-honor.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.tarif-honor') ? 'active' : '' }}">
    <i class="bi bi-sliders"></i> Tarif Honor Skema
</a>

<a href="{{ route('bendahara.rekening.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.rekening') ? 'active' : '' }}">
    <i class="bi bi-wallet"></i> Rekening Asesor
</a>

<a href="{{ route('bendahara.rekap-pendapatan') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.rekap-pendapatan') ? 'active' : '' }}">
    <i class="bi bi-graph-up-arrow"></i> Rekap Pendapatan
</a>

{{-- ── PENGELUARAN ────────────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>PENGELUARAN</span>
</div>

<a href="{{ route('bendahara.biaya-operasional.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.biaya-operasional') ? 'active' : '' }}">
    <i class="bi bi-cash-stack"></i> Biaya Operasional
</a>

<div class="sidebar-divider">
    <span>PENDAPATAN LUAR</span>
</div>

<a href="{{ route('bendahara.pendapatan-luar.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.pendapatan-luar') ? 'active' : '' }}">
    <i class="bi bi-cash-stack"></i> Pendapatan Luar
</a>

<a href="{{ route('bendahara.other-receivables.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.other-receivables') ? 'active' : '' }}">
    <i class="bi bi-cart"></i> Piutang Lainnya
</a>

{{-- ── LAPORAN ─────────────────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>LAPORAN</span>
</div>

<a href="{{ route('bendahara.laporan-keuangan.index') }}"
    class="nav-link {{ str_starts_with($route, 'bendahara.laporan-keuangan') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-spreadsheet"></i> Laporan Keuangan
</a>

<a href="{{ route('bendahara.laporan-keuangan.transaksi-harian') }}"
    class="nav-link {{ $route === 'bendahara.laporan-keuangan.transaksi-harian' ? 'active' : '' }}">
    <i class="bi bi-calendar-day"></i> Transaksi Harian
</a>

<a href="{{ route('bendahara.laporan-keuangan.buku-besar') }}"
    class="nav-link {{ $route === 'bendahara.laporan-keuangan.buku-besar' ? 'active' : '' }}">
    <i class="bi bi-journal-bookmark"></i> Buku Besar
</a>

<a href="{{ route('bendahara.coa.index') }}"
   class="nav-link {{ str_starts_with($route, 'bendahara.coa') ? 'active' : '' }}">
    <i class="bi bi-list-columns-reverse"></i> Chart of Account
</a>