@php
$route = Route::currentRouteName();

$pendingMandiriVerifications = \App\Models\Asesmen::where('is_collective', false)
->where('status', 'data_completed')
->whereNull('admin_verified_at')
->count();

$pendingCollectiveVerifications = \App\Models\Asesmen::where('is_collective', true)
->whereNotNull('tuk_verified_at')
->whereNull('admin_verified_at')
->where('status', 'data_completed')
->distinct('collective_batch_id')
->count('collective_batch_id');

$pendingAssignments = \App\Models\Asesmen::where('is_collective', false)
->where('status', 'verified')
->whereNull('assigned_tuk_id')
->whereNotNull('admin_verified_at')
->count();

$pendingPayments = \App\Models\Payment::where('status', 'pending')->count();
@endphp

{{-- Dashboard --}}
<a href="{{ route('admin.dashboard') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

{{-- Divider: MASTER DATA --}}
<div class="sidebar-divider">
    <span>MASTER DATA</span>
</div>

<a href="{{ route('admin.tuks') }}" class="nav-link {{ Str::startsWith($route, 'admin.tuks') ? 'active' : '' }}">
    <i class="bi bi-building"></i> Kelola TUK
</a>

<a href="{{ route('admin.skemas') }}" class="nav-link {{ Str::startsWith($route, 'admin.skemas') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Kelola Skema
</a>

{{-- ✅ BARU: Kelola Asesor --}}
<a href="{{ route('admin.asesors.index') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.asesors') ? 'active' : '' }}">
    <i class="bi bi-person-badge"></i> Kelola Asesor
    @php $expireCount = \App\Models\Asesor::where('status_reg', 'expire')->count(); @endphp
    @if($expireCount > 0)
    <span class="badge bg-warning ms-auto">{{ $expireCount }}</span>
    @endif
</a>

{{-- Divider: VERIFIKASI & BIAYA --}}
<div class="sidebar-divider">
    <span>Pendaftaran Asesi Mandiri</span>
</div>

<a href="{{ route('admin.mandiri.verifications') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.mandiri.verifications') ? 'active' : '' }}">
    <i class="bi bi-person-check"></i> Verifikasi Mandiri
    @if($pendingMandiriVerifications > 0)
    <span class="badge bg-warning ms-auto">{{ $pendingMandiriVerifications }}</span>
    @endif
</a>

<a href="{{ route('admin.mandiri.assignment') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.mandiri.assignment') ? 'active' : '' }}">
    <i class="bi bi-building-add"></i> Assignment ke TUK
    @if($pendingAssignments > 0)
    <span class="badge bg-primary ms-auto">{{ $pendingAssignments }}</span>
    @endif
</a>

<div class="sidebar-divider">
    <span>MANAJEMEN ASESMEN</span>
</div>

<a href="{{ route('admin.praasesmen.index') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.praasesmen.index') || Str::startsWith($route, 'admin.asesi.show') || Str::startsWith($route, 'admin.apl') ? 'active' : '' }}">
    <i class="bi bi-layers"></i> Manajemen Pra-Asesmen
    @if($pendingCollectiveVerifications > 0)
    <span class="badge bg-warning ms-auto">{{ $pendingCollectiveVerifications }}</span>
    @endif
</a>

<a href="{{ route('admin.schedules.index') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.schedules.index') ? 'active' : '' }}">
    <i class="bi bi-calendar-event"></i> Jadwal Asesmen
</a>

{{-- Divider: MONITORING --}}
<div class="sidebar-divider">
    <span>MONITORING</span>
</div>

<a href="{{ route('admin.payments') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.payments') ? 'active' : '' }}">
    <i class="bi bi-credit-card"></i> Monitor Pembayaran
    @if($pendingPayments > 0)
    <span class="badge bg-danger ms-auto">{{ $pendingPayments }}</span>
    @endif
</a>

{{-- Divider: DATA & LAPORAN --}}
<div class="sidebar-divider">
    <span>DATA & LAPORAN</span>
</div>

<a href="{{ route('admin.asesi') }}" class="nav-link {{ Str::startsWith($route, 'admin.asesi') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Semua Asesi
</a>

<a href="{{ route('admin.reports') }}" class="nav-link {{ Str::startsWith($route, 'admin.reports') ? 'active' : '' }}">
    <i class="bi bi-graph-up"></i> Laporan
</a>