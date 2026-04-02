@php $route = Route::currentRouteName(); @endphp
 
<a href="{{ route('manajer-sertifikasi.index') }}"
   class="nav-link {{ $route === 'manajer-sertifikasi.index' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
 
<div class="sidebar-divider"><span>Bank Soal</span></div>
 
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
 
<div class="sidebar-divider"><span>Distribusi</span></div>
 
<a href="{{ route('manajer-sertifikasi.distribusi') }}"
   class="nav-link {{ $route === 'manajer-sertifikasi.distribusi' || Str::startsWith($route, 'manajer-sertifikasi.jadwal') ? 'active' : '' }}">
    @php
        $jadwalBelum = \App\Models\Schedule::approved()
            ->whereDoesntHave('distribusiSoalTeori')->count();
    @endphp
    <i class="bi bi-send-check"></i> Distribusi ke Jadwal
    @if($jadwalBelum > 0)
    <span class="badge bg-warning text-dark ms-auto">{{ $jadwalBelum }}</span>
    @endif
</a>