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

<a href="{{ route('profile.show') }}"
   class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="bi bi-person-circle"></i>
    Profil Saya
</a>