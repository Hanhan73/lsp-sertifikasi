@extends('layouts.app')
@section('title', 'Jadwal Asesmen')
@section('page-title', 'Jadwal Asesmen')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

{{-- Filter tabs ────────────────────────────────────────── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-2">
        <div class="d-flex gap-2 flex-wrap">
            @foreach(['upcoming' => 'Mendatang', 'today' => 'Hari Ini', 'past' => 'Selesai'] as $key => $label)
            <a href="{{ route('asesor.schedule', ['filter' => $key]) }}"
               class="btn btn-sm {{ $filter === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Schedule list ──────────────────────────────────────── --}}
@forelse($schedules as $schedule)
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3">

            {{-- Date badge ── --}}
            <div class="text-center rounded-3 px-3 py-2 flex-shrink-0"
                 style="background:{{ $schedule->assessment_date->isToday() ? '#fef9c3' : '#f1f5f9' }}; min-width:60px;">
                <div class="fw-bold text-primary" style="font-size:1.4rem; line-height:1;">
                    {{ $schedule->assessment_date->format('d') }}
                </div>
                <div class="text-muted" style="font-size:.72rem;">
                    {{ $schedule->assessment_date->format('M Y') }}
                </div>
                @if($schedule->assessment_date->isToday())
                <span class="badge bg-warning text-dark mt-1" style="font-size:.6rem;">Hari ini</span>
                @endif
            </div>

            {{-- Info ── --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $schedule->skema->name ?? '-' }}</h6>
                        <div class="text-muted small">
                            <i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}
                            @if($schedule->location)
                            &bull; <i class="bi bi-geo-alt me-1"></i>{{ $schedule->location }}
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('asesor.schedule.detail', $schedule) }}" class="btn btn-sm btn-primary flex-shrink-0">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>
                </div>

                {{-- Asesi summary ── --}}
                <div class="mt-2 d-flex align-items-center gap-3 flex-wrap">
                    <span class="badge bg-secondary">{{ $schedule->asesmens->count() }} Asesi</span>
                    @php
                        $apl02Done   = $schedule->asesmens->filter(fn($a) => $a->apldua?->status === 'verified')->count();
                        $apl02Submit = $schedule->asesmens->filter(fn($a) => $a->apldua?->status === 'submitted')->count();
                    @endphp
                    @if($apl02Submit > 0)
                    <span class="badge bg-info">{{ $apl02Submit }} APL-02 perlu diverifikasi</span>
                    @endif
                    @if($apl02Done > 0)
                    <span class="badge bg-success">{{ $apl02Done }} APL-02 terverifikasi</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <i class="bi bi-calendar-x" style="font-size:3rem;"></i>
    <h5 class="mt-3">Tidak ada jadwal</h5>
    <p>Belum ada jadwal untuk filter yang dipilih.</p>
</div>
@endforelse

@endsection