@extends('layouts.app')
@section('title', 'Riwayat Honor — ' . $asesor->nama)
@section('page-title', 'Honor Asesor: ' . $asesor->nama)

@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('direktur.keuangan.honor') }}">Honor Asesor</a></li>
        <li class="breadcrumb-item active">{{ $asesor->nama }}</li>
    </ol>
</nav>

<div class="row g-4">

    {{-- Kiri: Info Asesor --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-circle me-1 text-primary"></i>Informasi Asesor
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted small">Nama</div>
                        <div class="fw-semibold">{{ $asesor->nama }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">No. Reg MET</div>
                        <div>{{ $asesor->no_reg_met ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Email</div>
                        <div>{{ $asesor->email }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Telepon</div>
                        <div>{{ $asesor->telepon ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kanan: Riwayat --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clock-history me-1 text-secondary"></i>Riwayat Honor
            </div>
            <div class="card-body p-0">
                @if($riwayat->isEmpty())
                <div class="text-center text-muted py-4">Belum ada riwayat pembayaran.</div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($riwayat as $honor)
                    <a href="{{ route('direktur.keuangan.honor.payment', $honor) }}"
                        class="list-group-item list-group-item-action px-3 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small">{{ $honor->nomor_kwitansi }}</div>
                                <div class="text-muted" style="font-size:.78rem;">
                                    {{ $honor->details->count() }} jadwal &bull;
                                    Rp {{ number_format($honor->total, 0, ',', '.') }}
                                </div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ optional($honor->tanggal_kwitansi)->translatedFormat('d M Y') }}
                                </div>
                            </div>
                            <span class="badge bg-{{ $honor->status_badge }} ms-2">
                                {{ $honor->status_label }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection