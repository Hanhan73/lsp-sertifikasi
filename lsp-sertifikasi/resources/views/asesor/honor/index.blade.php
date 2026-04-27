@extends('layouts.app')

@section('title', 'Honor Saya')
@section('page-title', 'Honor Asesor')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
        class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-cash-coin me-1 text-success"></i>Riwayat Honor
    </div>
    <div class="card-body p-0">
        @if($honors->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada honor yang dibuat.
        </div>
        @else
        <div class="list-group list-group-flush">
            @foreach($honors as $honor)
            <a href="{{ route('asesor.honor.show', $honor) }}" class="list-group-item list-group-item-action px-4 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">{{ $honor->nomor_kwitansi }}</div>
                        <div class="text-muted small">
                            {{ $honor->details->count() }} jadwal &bull;
                            Rp {{ number_format($honor->total, 0, ',', '.') }}
                        </div>
                        <div class="text-muted small">
                            {{ optional($honor->tanggal_kwitansi)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $honor->status_badge }}">{{ $honor->status_label }}</span>
                        @if($honor->isSudahDibayar())
                        <div class="text-danger small mt-1 fw-semibold">
                            <i class="bi bi-exclamation-circle me-1"></i>Perlu konfirmasi
                        </div>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection