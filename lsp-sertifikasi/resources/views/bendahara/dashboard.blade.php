@extends('layouts.app')
@section('title', 'Dashboard Bendahara')
@section('page-title', 'Dashboard Bendahara')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="card-body">
                <i class="bi bi-clock-history text-warning fs-2 mb-2 d-block"></i>
                <div class="fw-bold fs-3 text-warning">{{ $stats['pending'] }}</div>
                <div class="text-muted small">Menunggu Verifikasi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="card-body">
                <i class="bi bi-check-circle text-success fs-2 mb-2 d-block"></i>
                <div class="fw-bold fs-3 text-success">{{ $stats['verified'] }}</div>
                <div class="text-muted small">Terverifikasi (bulan ini)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="card-body">
                <i class="bi bi-x-circle text-danger fs-2 mb-2 d-block"></i>
                <div class="fw-bold fs-3 text-danger">{{ $stats['rejected'] }}</div>
                <div class="text-muted small">Ditolak (bulan ini)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="card-body">
                <i class="bi bi-cash-stack text-primary fs-2 mb-2 d-block"></i>
                <div class="fw-bold fs-3 text-primary" style="font-size:1.2rem!important;">
                    Rp {{ number_format($stats['total_bulan'], 0, ',', '.') }}
                </div>
                <div class="text-muted small">Total Terverifikasi Bulan Ini</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <div class="fw-semibold"><i class="bi bi-clock text-warning me-2"></i>Menunggu Verifikasi</div>
        <a href="{{ route('bendahara.payments.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning">
            Lihat Semua
        </a>
    </div>
    @if($pending->isEmpty())
    <div class="card-body text-center py-4 text-muted">
        <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i>
        Tidak ada pembayaran yang menunggu verifikasi.
    </div>
    @else
    <div class="list-group list-group-flush">
        @foreach($pending as $p)
        <a href="{{ route('bendahara.payments.show', $p) }}"
           class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-4 py-3">
            <div class="rounded-circle bg-warning bg-opacity-15 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:42px;height:42px;">
                <i class="bi bi-cash text-warning"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ $p->asesmen->full_name }}</div>
                <div class="small text-muted">{{ $p->asesmen->skema->name ?? '-' }} &bull; {{ strtoupper($p->method) }}</div>
            </div>
            <div class="text-end flex-shrink-0">
                <div class="fw-bold text-success">Rp {{ number_format($p->amount, 0, ',', '.') }}</div>
                <div class="small text-muted">{{ $p->created_at->diffForHumans() }}</div>
            </div>
            <i class="bi bi-chevron-right text-muted small"></i>
        </a>
        @endforeach
    </div>
    @endif
</div>

@endsection