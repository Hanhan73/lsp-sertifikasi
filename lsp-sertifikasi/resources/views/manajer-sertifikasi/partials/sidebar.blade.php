{{-- resources/views/manajer-sertifikasi/partials/sidebar.blade.php --}}
@php
$route = Route::currentRouteName();
@endphp

{{-- Dashboard --}}
<a href="{{ route('manajer-sertifikasi.index') }}"
    class="nav-link {{ $route === 'manajer-sertifikasi.index' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

{{-- ===== BANK SOAL ===== --}}
<div class="sidebar-divider">
    <span>Bank Soal</span>
</div>

<a href="{{ route('manajer-sertifikasi.bank-soal.index') }}"
    class="nav-link {{ Str::startsWith($route, 'manajer-sertifikasi.bank-soal') ? 'active' : '' }}">
    <i class="bi bi-collection"></i> Bank Soal
    @php
        $totalSoal = \App\Models\SoalObservasi::count()
                   + \App\Models\SoalTeori::count()
                   + \App\Models\Portofolio::count();
    @endphp
    @if($totalSoal > 0)
    <span class="badge bg-light text-primary ms-auto">{{ $totalSoal }}</span>
    @endif
</a>

{{-- ===== DISTRIBUSI ===== --}}
<div class="sidebar-divider">
    <span>Distribusi</span>
</div>

<a href="{{ route('manajer-sertifikasi.index') }}"
    class="nav-link {{ Str::startsWith($route, 'manajer-sertifikasi.jadwal') ? 'active' : '' }}">
    @php
    $jadwalBelumLengkap = \App\Models\Schedule::approved()
        ->whereDoesntHave('distribusiSoalTeori')
        ->count();
    @endphp
    <i class="bi bi-send-check"></i> Distribusi ke Jadwal
    @if($jadwalBelumLengkap > 0)
    <span class="badge bg-warning ms-auto">{{ $jadwalBelumLengkap }}</span>
    @endif
</a>