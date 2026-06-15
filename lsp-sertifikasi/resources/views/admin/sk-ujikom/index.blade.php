@extends('layouts.app')
@section('title', 'SK Hasil Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">SK Hasil Ujikom</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">
            Generate Surat Keputusan penetapan hasil uji kompetensi per batch.
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
        <p class="mt-3 mb-0">Belum ada batch dengan Berita Acara.</p>
    </div>
</div>
@else

<div class="d-flex gap-2 flex-wrap mb-3" style="font-size:.8rem;">
    <span class="badge bg-secondary px-3 py-2">Belum di-generate</span>
    <span class="badge bg-success px-3 py-2">SK Tersedia</span>
</div>

<div class="row g-3">
@foreach($data as $item)
@php $sk = $item['sk']; @endphp
<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center g-3">

                <div class="col-lg-5">
                    <div class="fw-semibold mb-1">{{ $item['skema']?->name ?? '-' }}</div>
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

                <div class="col-lg-3">
                    @if($sk)
                    <span class="badge bg-success px-3 py-2">SK Tersedia</span>
                    <div class="small text-muted mt-1 font-monospace">{{ $sk->nomor_sk }}</div>
                    @elseif($item['siap'] && $item['total_k'] > 0)
                    <span class="badge bg-light text-secondary border px-3 py-2">Belum di-generate</span>
                    @else
                    <span class="badge bg-light text-muted border px-3 py-2">
                        <i class="bi bi-hourglass me-1"></i>BA belum lengkap / tidak ada K
                    </span>
                    @endif
                </div>

                <div class="col-lg-4 d-flex justify-content-end gap-2 flex-wrap">
                    @if(!$sk && $item['siap'] && $item['total_k'] > 0)
                    <a href="{{ route('admin.sk-ujikom.create', $item['batch_id']) }}"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-file-earmark-plus me-1"></i>Generate SK
                    </a>
                    @elseif($sk)
                    <a href="{{ route('admin.sk-ujikom.show', $sk) }}"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>
                    @if($sk->hasSk())
                    <a href="{{ route('admin.sk-ujikom.download', $sk) }}"
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
