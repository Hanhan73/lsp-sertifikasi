@extends('layouts.app')
@section('title', 'Pengajuan SK Hasil Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Pengajuan SK Hasil Ujikom</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">
            SK diterbitkan per batch. Pastikan semua Berita Acara sudah disubmit asesor sebelum mengajukan.
        </p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
</div>
@endif
@if(session('info'))
<div class="alert alert-info border-0 shadow-sm">
    <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
</div>
@endif

@if($data->isEmpty())
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-file-earmark-x" style="font-size:2.5rem;opacity:.3;"></i>
        <p class="mt-3 mb-0">Belum ada batch yang siap diajukan SK-nya.</p>
        <p class="small">Pastikan asesor sudah submit Berita Acara terlebih dahulu.</p>
    </div>
</div>
@else

{{-- Legend status --}}
<div class="d-flex gap-2 flex-wrap mb-3" style="font-size:.8rem;">
    <span class="badge bg-secondary px-3 py-2">Draft / Belum Diajukan</span>
    <span class="badge bg-warning text-dark px-3 py-2">Menunggu Persetujuan Direktur</span>
    <span class="badge bg-success px-3 py-2">Disetujui — SK Tersedia</span>
    <span class="badge bg-danger px-3 py-2">Ditolak</span>
</div>

<div class="row g-3">
@foreach($data as $item)
@php
    $sk     = $item['sk'];
    $status = $sk?->status ?? null;
@endphp

<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center g-3">

                {{-- Info Batch --}}
                <div class="col-lg-5">
                    <div class="fw-semibold mb-1">
                        {{ $item['skema']?->name ?? '-' }}
                    </div>
                    <div class="text-muted small mb-1">
                        <i class="bi bi-building me-1"></i>{{ $item['tuk']?->name ?? '-' }}
                        &nbsp;·&nbsp;
                        <code style="font-size:.75rem;">{{ $item['batch_id'] }}</code>
                    </div>
                    <div class="d-flex gap-3 small">
                        <span><i class="bi bi-people me-1 text-primary"></i>{{ $item['total_asesi'] }} peserta</span>
                        <span><i class="bi bi-calendar3 me-1 text-secondary"></i>{{ $item['total_ba'] }}/{{ $item['total_jadwal'] }} BA</span>
                        <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>{{ $item['total_k'] }} Kompeten</span>
                        @if($item['total_bk'] > 0)
                        <span class="text-danger"><i class="bi bi-x-circle me-1"></i>{{ $item['total_bk'] }} BK</span>
                        @endif
                    </div>
                </div>

                {{-- Status SK --}}
                <div class="col-lg-3">
                    @if($sk)
                    <span class="badge bg-{{ $sk->status_badge }} px-3 py-2">
                        {{ $sk->status_label }}
                    </span>
                    @if($sk->isApproved())
                    <div class="small text-muted mt-1">
                        <code>{{ $sk->nomor_sk }}</code>
                    </div>
                    @endif
                    @if($sk->isRejected())
                    <div class="small text-danger mt-1">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ Str::limit($sk->catatan_direktur, 60) }}
                    </div>
                    @endif
                    @else
                    @if($item['siap'])
                    <span class="badge bg-light text-secondary border px-3 py-2">Belum Diajukan</span>
                    @else
                    <span class="badge bg-light text-muted border px-3 py-2">
                        <i class="bi bi-hourglass me-1"></i>Berita Acara belum lengkap
                    </span>
                    @endif
                    @endif
                </div>

                {{-- Aksi --}}
                <div class="col-lg-4 d-flex justify-content-end gap-2 flex-wrap">
                    @if(!$sk && $item['siap'] && $item['total_k'] > 0)
                    <a href="{{ route('manajer-sertifikasi.sk-ujikom.create', $item['batch_id']) }}"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i>Ajukan SK
                    </a>
                    @elseif($sk)
                    <a href="{{ route('manajer-sertifikasi.sk-ujikom.show', $sk) }}"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>Lihat Detail
                    </a>
                    @if($sk->isApproved() && $sk->hasSk())
                    <a href="{{ route('manajer-sertifikasi.sk-ujikom.download', $sk) }}"
                       class="btn btn-success btn-sm">
                        <i class="bi bi-download me-1"></i>Unduh SK
                    </a>
                    @endif
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endforeach
</div>
@endif

@endsection