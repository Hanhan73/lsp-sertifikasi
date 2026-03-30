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

{{-- 1. Soal Observasi (di dalamnya ada paket A, B, C, D) --}}
<a href="{{ route('manajer-sertifikasi.soal-observasi.index') }}"
    class="nav-link {{ Str::startsWith($route, 'manajer-sertifikasi.soal-observasi') ? 'active' : '' }}">
    <i class="bi bi-eye"></i> Soal Observasi
    @php $totalObservasi = \App\Models\SoalObservasi::count(); @endphp
    @if($totalObservasi > 0)
    <span class="badge bg-light text-primary ms-auto">{{ $totalObservasi }}</span>
    @endif
</a>

{{-- 2. Soal Teori PG --}}
<a href="{{ route('manajer-sertifikasi.soal-teori.index') }}"
    class="nav-link {{ Str::startsWith($route, 'manajer-sertifikasi.soal-teori') ? 'active' : '' }}">
    <i class="bi bi-journal-text"></i> Soal Teori (PG)
    @php $totalTeori = \App\Models\SoalTeori::count(); @endphp
    @if($totalTeori > 0)
    <span class="badge bg-light text-primary ms-auto">{{ $totalTeori }}</span>
    @endif
</a>

{{-- 3. Portofolio --}}
<a href="{{ route('manajer-sertifikasi.portofolio.index') }}"
    class="nav-link {{ Str::startsWith($route, 'manajer-sertifikasi.portofolio') ? 'active' : '' }}">
    <i class="bi bi-briefcase"></i> Portofolio
    @php $totalPorto = \App\Models\Portofolio::count(); @endphp
    @if($totalPorto > 0)
    <span class="badge bg-light text-primary ms-auto">{{ $totalPorto }}</span>
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