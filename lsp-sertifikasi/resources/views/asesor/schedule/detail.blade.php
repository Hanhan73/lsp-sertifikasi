@extends('layouts.app')
@section('title', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Asesmen')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

{{-- Header jadwal ──────────────────────────────────────── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start gap-4 flex-wrap">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    @if($schedule->assessment_date->isToday())
                    <span class="badge bg-warning text-dark">Hari Ini</span>
                    @elseif($schedule->assessment_date->isPast())
                    <span class="badge bg-secondary">Selesai</span>
                    @else
                    <span class="badge bg-primary">Mendatang</span>
                    @endif
                </div>
                <h4 class="fw-bold mb-1">{{ $schedule->skema->name ?? '-' }}</h4>
                <div class="text-muted small d-flex flex-wrap gap-3">
                    <span><i class="bi bi-calendar3 me-1"></i>{{ $schedule->assessment_date->translatedFormat('l, d F Y') }}</span>
                    <span><i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}</span>
                    <span><i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}</span>
                    @if($schedule->location)
                    <span><i class="bi bi-geo-alt me-1"></i>{{ $schedule->location }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('asesor.schedule') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        @if($schedule->notes)
        <div class="mt-3 p-3 rounded" style="background:#f8fafc; border-left:3px solid #627be9;">
            <small class="text-muted"><i class="bi bi-sticky me-1"></i>{{ $schedule->notes }}</small>
        </div>
        @endif
    </div>
</div>

{{-- Stats mini ─────────────────────────────────────────── --}}
@php
    $asesmens   = $schedule->asesmens;
    $totalAsesi = $asesmens->count();
    $apl01Done  = $asesmens->filter(fn($a) => $a->aplsatu?->status === 'verified')->count();
    $apl02Subm  = $asesmens->filter(fn($a) => $a->apldua?->status === 'submitted')->count();
    $apl02Ver   = $asesmens->filter(fn($a) => $a->apldua?->status === 'verified')->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-primary">{{ $totalAsesi }}</div>
            <div class="small text-muted">Total Asesi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-success">{{ $apl01Done }}</div>
            <div class="small text-muted">APL-01 Terverifikasi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-info">{{ $apl02Subm }}</div>
            <div class="small text-muted">APL-02 Perlu Diverifikasi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-success">{{ $apl02Ver }}</div>
            <div class="small text-muted">APL-02 Terverifikasi</div>
        </div>
    </div>
</div>

{{-- Daftar asesi ────────────────────────────────────────── --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Daftar Peserta Asesmen</h6>
    </div>
    <div class="card-body p-0">
        @forelse($asesmens as $idx => $asesmen)
        @php
            $aplsatu = $asesmen->aplsatu;
            $apldua  = $asesmen->apldua;
            $needsVerify = $apldua?->status === 'submitted';
        @endphp
        <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom
            {{ $needsVerify ? 'bg-info bg-opacity-5' : '' }}">

            {{-- No ── --}}
            <div class="text-muted fw-bold" style="min-width:28px;">{{ $idx + 1 }}</div>

            {{-- Foto ── --}}
            @if($asesmen->photo_path)
            <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                 class="rounded-circle border" style="width:44px;height:44px;object-fit:cover;" alt="foto">
            @else
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:44px;height:44px;font-size:1.1rem;">
                {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
            </div>
            @endif

            {{-- Info ── --}}
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ $asesmen->full_name }}</div>
                <div class="text-muted small">NIK: {{ $asesmen->nik }}</div>
            </div>

            {{-- Status badges ── --}}
            <div class="d-flex gap-2 align-items-center flex-wrap">
                {{-- APL-01 ── --}}
                @if($aplsatu)
                <span class="badge bg-{{ $aplsatu->status === 'verified' ? 'success' : ($aplsatu->status === 'submitted' ? 'info' : 'secondary') }}"
                      style="font-size:.7rem;">
                    APL-01: {{ ucfirst($aplsatu->status) }}
                </span>
                @else
                <span class="badge bg-light text-muted border" style="font-size:.7rem;">APL-01: Belum Ada</span>
                @endif

                {{-- APL-02 ── --}}
                @if($apldua)
                <span class="badge bg-{{ $apldua->status_badge }}" style="font-size:.7rem;">
                    APL-02: {{ $apldua->status_label }}
                </span>
                @if($needsVerify)
                <span class="badge bg-warning text-dark" style="font-size:.68rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>Perlu Verifikasi
                </span>
                @endif
                @else
                <span class="badge bg-light text-muted border" style="font-size:.7rem;">APL-02: Belum Ada</span>
                @endif
            </div>

            {{-- Action ── --}}
            <a href="{{ route('asesor.asesi.detail', [$schedule, $asesmen]) }}"
               class="btn btn-sm {{ $needsVerify ? 'btn-warning' : 'btn-outline-primary' }} flex-shrink-0">
                <i class="bi bi-{{ $needsVerify ? 'pen-fill' : 'eye' }} me-1"></i>
                {{ $needsVerify ? 'Verifikasi' : 'Detail' }}
            </a>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-x" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Belum ada peserta di jadwal ini.</p>
        </div>
        @endforelse
    </div>
</div>

@endsection