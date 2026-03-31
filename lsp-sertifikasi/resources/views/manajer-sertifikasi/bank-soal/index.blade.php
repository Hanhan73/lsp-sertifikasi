{{-- resources/views/manajer-sertifikasi/bank-soal/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Bank Soal')
@section('breadcrumb', 'Bank Soal')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <h4 class="fw-bold mb-1">Bank Soal</h4>
    <p class="text-muted mb-0" style="font-size:.875rem">
        Kelola soal observasi, soal teori (PG), dan portofolio per skema sertifikasi.
    </p>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.875rem">
    <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($skemas->isEmpty())
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-collection" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
        <p class="fw-semibold mb-0">Belum ada skema aktif</p>
        <small>Aktifkan skema terlebih dahulu di menu Admin</small>
    </div>
</div>
@else
<div class="row g-3">
    @foreach($skemas as $skema)
    @php
        $statObservasi = $stats[$skema->id]['observasi'] ?? 0;
        $statTeori     = $stats[$skema->id]['teori'] ?? 0;
        $statPorto     = $stats[$skema->id]['portofolio'] ?? 0;
        $totalSoal     = $statObservasi + $statTeori + $statPorto;
    @endphp
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
            {{-- Colored top strip --}}
            <div style="height:4px;background:linear-gradient(90deg,#2563eb,#7c3aed)"></div>
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="fw-bold mb-1" style="font-size:1rem">{{ $skema->name }}</div>
                        <span class="badge rounded-pill"
                              style="background:#eff6ff;color:#2563eb;font-size:.72rem;font-weight:600">
                            {{ $skema->code }}
                        </span>
                        <span class="badge rounded-pill ms-1"
                              style="background:#f5f3ff;color:#7c3aed;font-size:.72rem;font-weight:600">
                            {{ $skema->jenis_label }}
                        </span>
                    </div>
                    @if($totalSoal === 0)
                    <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:.7rem">
                        <i class="bi bi-exclamation-circle me-1"></i>Belum ada soal
                    </span>
                    @else
                    <span class="badge bg-success-subtle text-success-emphasis" style="font-size:.7rem">
                        <i class="bi bi-check-circle me-1"></i>{{ $totalSoal }} soal
                    </span>
                    @endif
                </div>

                {{-- Stats row --}}
                <div class="d-flex gap-2 mb-4">
                    <div class="flex-fill text-center p-2 rounded-3"
                         style="background:#f0f9ff;border:1px solid #bae6fd">
                        <div class="fw-bold" style="font-size:1.1rem;color:#0284c7">{{ $statObservasi }}</div>
                        <div style="font-size:.65rem;color:#6b7280;font-weight:600">OBSERVASI</div>
                    </div>
                    <div class="flex-fill text-center p-2 rounded-3"
                         style="background:#f0fdf4;border:1px solid #bbf7d0">
                        <div class="fw-bold" style="font-size:1.1rem;color:#16a34a">{{ $statTeori }}</div>
                        <div style="font-size:.65rem;color:#6b7280;font-weight:600">SOAL TEORI</div>
                    </div>
                    <div class="flex-fill text-center p-2 rounded-3"
                         style="background:#fdf4ff;border:1px solid #e9d5ff">
                        <div class="fw-bold" style="font-size:1.1rem;color:#7c3aed">{{ $statPorto }}</div>
                        <div style="font-size:.65rem;color:#6b7280;font-weight:600">PORTOFOLIO</div>
                    </div>
                </div>

                <a href="{{ route('manajer-sertifikasi.bank-soal.show', $skema) }}"
                   class="btn btn-primary w-100 btn-sm">
                    <i class="bi bi-pencil-square me-1"></i> Kelola Soal
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection