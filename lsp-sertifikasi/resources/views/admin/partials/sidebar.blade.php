@php
$route = Route::currentRouteName();
$pendingVerifications = \App\Models\Asesmen::where('status', 'data_completed')->whereNull('tuk_verified_by')->count();
$pendingPayments = \App\Models\Payment::where('status', 'pending')->count();
@endphp

<a href="{{ route('admin.dashboard') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<a href="{{ route('admin.tuks') }}" class="nav-link {{ Str::startsWith($route, 'admin.tuks') ? 'active' : '' }}">
    <i class="bi bi-building"></i> Kelola TUK
</a>

<a href="{{ route('admin.skemas') }}" class="nav-link {{ Str::startsWith($route, 'admin.skemas') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> Kelola Skema
</a>

<a href="{{ route('admin.verifications') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.verifications') ? 'active' : '' }}">
    <i class="bi bi-cash-coin"></i> Penetapan Biaya

</a>

<a href="{{ route('admin.payments') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.payments') ? 'active' : '' }}">
    <i class="bi bi-credit-card"></i> Monitor Pembayaran

</a>

<a href="{{ route('admin.assessments') }}"
    class="nav-link {{ Str::startsWith($route, 'admin.assessments') ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> Input Hasil Asesmen
</a>

<a href="{{ route('admin.asesi') }}" class="nav-link {{ Str::startsWith($route, 'admin.asesi') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Semua Asesi
</a>

<a href="{{ route('admin.reports') }}" class="nav-link {{ Str::startsWith($route, 'admin.reports') ? 'active' : '' }}">
    <i class="bi bi-graph-up"></i> Laporan
</a>