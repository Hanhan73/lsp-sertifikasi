{{-- resources/views/bendahara/rekening/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Rekening Bank Asesor')
@section('page-title', 'Rekening Bank Asesor')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center gap-2">
        <i class="bi bi-credit-card text-success"></i>
        <span class="fw-semibold">Daftar Rekening Bank Asesor</span>
        <span class="badge bg-secondary ms-auto">{{ $asesors->count() }} Asesor</span>
    </div>
    <div class="card-body p-0">
        @if($asesors->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada data asesor.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Asesor</th>
                        <th>No. Reg MET</th>
                        <th class="text-center">Jumlah Rekening</th>
                        <th class="text-center">Rekening Utama</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesors as $i => $asesor)
                    @php $utama = $asesor->rekenings->firstWhere('is_utama', true); @endphp
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $asesor->nama }}</div>
                            <div class="text-muted small">{{ $asesor->email }}</div>
                        </td>
                        <td class="small">{{ $asesor->no_reg_met ?? '-' }}</td>
                        <td class="text-center">
                            @if($asesor->rekenings->count() > 0)
                                <span class="badge bg-success">{{ $asesor->rekenings->count() }}</span>
                            @else
                                <span class="badge bg-secondary">0</span>
                            @endif
                        </td>
                        <td class="text-center small">
                            @if($utama)
                                <div class="fw-semibold">{{ $utama->nama_bank }}</div>
                                <div class="font-monospace text-muted" style="font-size:.78rem;">{{ $utama->nomor_rekening }}</div>
                            @else
                                <span class="text-muted fst-italic">Belum diset</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('bendahara.rekening.show', $asesor) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Kelola
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection