@php $route = Route::currentRouteName(); @endphp

<a href="{{ route('manajer-sertifikasi.index') }}"
    class="nav-link {{ $route === 'manajer-sertifikasi.index' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<div class="sidebar-divider"><span>Bank Soal</span></div>

<a href="{{ route('manajer-sertifikasi.bank-soal.index') }}"
    class="nav-link {{ Str::startsWith($route, 'manajer-sertifikasi.bank-soal') ? 'active' : '' }}">
    <i class="bi bi-collection"></i> Bank Soal
</a>

<div class="sidebar-divider"><span>Distribusi</span></div>

<a href="{{ route('manajer-sertifikasi.distribusi') }}"
    class="nav-link {{ $route === 'manajer-sertifikasi.distribusi' || Str::startsWith($route, 'manajer-sertifikasi.jadwal') ? 'active' : '' }}">
    <i class="bi bi-send-check"></i> Distribusi ke Jadwal
    @php
    $jadwalBelum = \App\Models\Schedule::approved()
    ->whereDoesntHave('distribusiSoalTeori')->count();
    @endphp
    @if($jadwalBelum > 0)
    <span class="badge bg-warning text-dark ms-auto">{{ $jadwalBelum }}</span>
    @endif
</a>

<a href="{{ route('manajer-sertifikasi.export-hasil-teori.index') }}"
   class="nav-link {{ request()->routeIs('manajer-sertifikasi.export-hasil-teori.*') ? 'active' : '' }}">
    <i class="bi bi-file-earmark-arrow-down me-2"></i>Export Hasil Teori
</a>

<div class="sidebar-divider"><span>SK Ujikom</span></div>
 
<a href="{{ route('manajer-sertifikasi.sk-ujikom.index') }}"
   class="nav-link {{ Str::startsWith($route, 'manajer-sertifikasi.sk-ujikom') ? 'active' : '' }}">
    @php
        $pendingSk = \App\Models\SkHasilUjikom::where('status', 'rejected')->count();
    @endphp
    <i class="bi bi-file-earmark-ruled"></i> Pengajuan SK Ujikom
    @if($pendingSk > 0)
    <span class="badge bg-danger ms-auto">{{ $pendingSk }}</span>
    @endif
</a>