<a href="{{ route('tuk.dashboard') }}" class="nav-link {{ request()->routeIs('tuk.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="{{ route('tuk.collective') }}" class="nav-link {{ request()->routeIs('tuk.collective*') ? 'active' : '' }}">
    <i class="bi bi-person-plus"></i> Pendaftaran Kolektif
</a>
<a href="{{ route('tuk.asesi') }}" class="nav-link {{ request()->routeIs('tuk.asesi*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Daftar Asesi
    @if(($stats['pending_payment'] ?? 0) > 0)
    <span class="badge bg-danger ms-2">{{ $stats['pending_payment'] }}</span>
    @endif
</a>
<a href="{{ route('tuk.schedules') }}" class="nav-link {{ request()->routeIs('tuk.schedules*') ? 'active' : '' }}">
    <i class="bi bi-calendar-event"></i> Penjadwalan
</a>
<a href="{{ route('tuk.verifications') }}"
    class="nav-link {{ request()->routeIs('tuk.verifications*') ? 'active' : '' }}">
    <i class="bi bi-check-circle"></i> Verifikasi Asesi
    @if(($stats['pending_verification'] ?? 0) > 0)
    <span class="badge bg-warning ms-2">{{ $stats['pending_verification'] }}</span>
    @endif
</a>