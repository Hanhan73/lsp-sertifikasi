{{-- resources/views/direktur/partials/sidebar.blade.php --}}

<a href="{{ route('direktur.dashboard') }}"
    class="nav-link {{ request()->routeIs('direktur.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>

<a href="{{ route('direktur.schedules.index') }}"
    class="nav-link {{ request()->routeIs('direktur.schedules.*') ? 'active' : '' }}">
    <i class="bi bi-calendar-check"></i>
    Approval Jadwal
    @php $pending = \App\Models\Schedule::pendingApproval()->count(); @endphp
    @if($pending > 0)
    <span class="badge bg-warning text-dark">{{ $pending }}</span>
    @endif
</a>

<a href="{{ route('direktur.sk-ujikom.index') }}"
    class="nav-link {{ request()->routeIs('direktur.sk-ujikom.*') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-ruled"></i>
    SK Hasil Ujikom
    @php $pendingSkDir = \App\Models\SkHasilUjikom::where('status', 'submitted')->count(); @endphp
    @if($pendingSkDir > 0)
    <span class="badge bg-warning text-dark">{{ $pendingSkDir }}</span>
    @endif
</a>

{{-- ── KEUANGAN ────────────────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>KEUANGAN</span>
</div>

<a href="{{ route('direktur.keuangan.index') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.index') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-spreadsheet"></i>
    Laporan Keuangan
</a>

<a href="{{ route('direktur.keuangan.pembayaran-kolektif') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.pembayaran-kolektif*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    Pembayaran Kolektif
</a>

<a href="{{ route('direktur.keuangan.pembayaran-mandiri') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.pembayaran-mandiri') ? 'active' : '' }}">
    <i class="bi bi-person"></i>
    Pembayaran Individu
</a>

<a href="{{ route('direktur.keuangan.honor') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.honor*') ? 'active' : '' }}">
    <i class="bi bi-person-badge"></i>
    Honor Asesor
</a>

<a href="{{ route('direktur.keuangan.rekap-pendapatan') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.rekap-pendapatan') ? 'active' : '' }}">
    <i class="bi bi-graph-up-arrow"></i>
    Rekap Pendapatan
</a>

<a href="{{ route('direktur.keuangan.biaya-operasional') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.biaya-operasional') ? 'active' : '' }}">
    <i class="bi bi-cash-stack"></i>
    Biaya Operasional
</a>

<a href="{{ route('direktur.keuangan.transaksi-harian') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.transaksi-harian') ? 'active' : '' }}">
    <i class="bi bi-calendar-day"></i>
    Transaksi Harian
</a>

<a href="{{ route('direktur.keuangan.buku-besar') }}"
    class="nav-link {{ request()->routeIs('direktur.keuangan.buku-besar') ? 'active' : '' }}">
    <i class="bi bi-journal-bookmark"></i>
    Buku Besar
</a>

{{-- ── AKUN ────────────────────────────────────────────────────────── --}}
<div class="sidebar-divider">
    <span>AKUN</span>
</div>

<a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="bi bi-person-circle"></i>
    Profil Saya
</a>