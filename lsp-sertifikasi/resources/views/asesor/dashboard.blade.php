@extends('layouts.app')
@section('title', 'Dashboard Asesor')
@section('page-title', 'Dashboard Asesor')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

{{-- Greeting ──────────────────────────────────────────── --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <img src="{{ $asesor->foto_url }}" class="rounded-circle border" style="width:56px;height:56px;object-fit:cover;"
        alt="foto">
    <div>
        <h5 class="mb-0 fw-bold">Selamat datang, {{ $asesor->nama }}</h5>
        <small class="text-muted">No. Reg: {{ $asesor->no_reg_met }} &bull;
            <span class="badge bg-{{ $asesor->status_badge }}">{{ $asesor->status_label }}</span>
        </small>
    </div>
</div>

{{-- Stats ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning">{{ $stats['today'] }}</div>
                <div class="small text-muted">Jadwal Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-primary">{{ $stats['upcoming'] }}</div>
                <div class="small text-muted">Jadwal Mendatang</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-secondary">{{ $stats['past'] }}</div>
                <div class="small text-muted">Jadwal Selesai</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-success">{{ $stats['total_asesi'] }}</div>
                <div class="small text-muted">Total Asesi</div>
            </div>
        </div>
    </div>
</div>

{{-- Jadwal Hari Ini ────────────────────────────────────── --}}
@if($todaySchedules->isNotEmpty())
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-warning text-white">
        <i class="bi bi-calendar-check me-2"></i>Jadwal Hari Ini
    </div>
    <div class="card-body p-0">
        @foreach($todaySchedules as $schedule)
        <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ $schedule->skema->name ?? '-' }}</div>
                <small class="text-muted">
                    {{ $schedule->tuk->name ?? '-' }} &bull;
                    {{ $schedule->start_time }} – {{ $schedule->end_time }} &bull;
                    {{ $schedule->asesmens->count() }} asesi
                </small>
            </div>
            <a href="{{ route('asesor.schedule.detail', $schedule) }}" class="btn btn-sm btn-warning">
                <i class="bi bi-eye me-1"></i>Lihat
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Jadwal Mendatang ───────────────────────────────────── --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>Jadwal Mendatang</h6>
        <a href="{{ route('asesor.schedule') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        @forelse($upcomingSchedules as $schedule)
        <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
            <div class="text-center" style="min-width:48px;">
                <div class="fw-bold text-primary" style="font-size:1.2rem;">
                    {{ $schedule->assessment_date->translatedFormat('d') }}</div>
                <div class="text-muted" style="font-size:.72rem;">{{ $schedule->assessment_date->translatedFormat('M Y') }}</div>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold small">{{ $schedule->skema->name ?? '-' }}</div>
                <div class="text-muted" style="font-size:.78rem;">
                    {{ $schedule->tuk->name ?? '-' }} &bull;
                    {{ $schedule->time_range ?? ($schedule->start_time . ' – ' . $schedule->end_time) }} &bull;
                    {{ $schedule->asesmens->count() }} asesi
                </div>
            </div>
            <a href="{{ route('asesor.schedule.detail', $schedule) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i>
            </a>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-0">Belum ada jadwal mendatang.</p>
        </div>
        @endforelse
    </div>
</div>

@endsection