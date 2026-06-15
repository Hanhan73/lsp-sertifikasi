@extends('layouts.app')
@section('title', 'Detail SK Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom › Detail')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.sk-ujikom.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Detail SK Hasil Ujikom</h4>
            <p class="text-muted mb-0" style="font-size:.875rem;">
                Batch: <code>{{ $skUjikom->collective_batch_id }}</code> &nbsp;·&nbsp;
                {{ $first?->skema?->name ?? '-' }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($skUjikom->hasSk())
            <form action="{{ route('admin.sk-ujikom.regenerate', $skUjikom) }}" method="POST"
                  onsubmit="return confirm('Regenerate ulang PDF SK?')">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i>Regenerate PDF
                </button>
            </form>
            <a href="{{ route('admin.sk-ujikom.download', $skUjikom) }}" class="btn btn-success">
                <i class="bi bi-download me-2"></i>Unduh SK PDF
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm mb-4">
    <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
</div>
@endif

{{-- Status Banner --}}
<div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-3 alert-success">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div>
        <div class="fw-semibold">SK Disetujui</div>
        <div class="small">Digenerate pada {{ $skUjikom->approved_at?->translatedFormat('d M Y H:i') }}</div>
    </div>
</div>

<div class="row g-4">

    {{-- ── Kiri: Info SK ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Info SK
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0" style="font-size:.875rem;">
                    <tr>
                        <td class="text-muted" style="width:130px;">Nomor SK</td>
                        <td class="fw-semibold font-monospace" style="font-size:.8rem;">{{ $skUjikom->nomor_sk }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Pleno</td>
                        <td>{{ $skUjikom->tanggal_pleno?->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dikeluarkan di</td>
                        <td>{{ $skUjikom->tempat_dikeluarkan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Digenerate</td>
                        <td class="small">{{ $skUjikom->approved_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">PDF</td>
                        <td>
                            @if($skUjikom->hasSk())
                            <span class="badge bg-success">Tersedia</span>
                            @else
                            <span class="badge bg-secondary">Belum ada</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Jadwal --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar3 me-2 text-secondary"></i>Jadwal Terkait
            </div>
            <div class="card-body p-0">
                @foreach($schedules as $s)
                <div class="px-3 py-2 border-bottom" style="font-size:.83rem;">
                    <div class="fw-semibold">{{ $s->assessment_date->translatedFormat('d M Y') }}</div>
                    <div class="text-muted">{{ $s->asesor?->nama ?? '-' }}</div>
                    @if($s->asesor?->no_reg_met)
                    <div class="text-muted" style="font-size:.78rem;">{{ $s->asesor->no_reg_met }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Kanan: Peserta Kompeten ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <span class="fw-semibold">
                    <i class="bi bi-people me-2 text-success"></i>Peserta Kompeten (K)
                </span>
                <span class="badge bg-success px-3">{{ $pesertaKompeten->count() }} orang</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle" style="font-size:.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>Asesor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastAsesorId = null; @endphp
                        @foreach($pesertaKompeten as $i => $asesi)
                        @php
                            $asesor = $asesi->_asesor;
                            $asesorId = $asesor?->id;
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}.</td>
                            <td class="fw-semibold">{{ $asesi->full_name }}</td>
                            <td class="text-muted small">
                                @if($asesorId !== $lastAsesorId)
                                    {{ $asesor?->nama ?? '-' }}
                                    @if($asesor?->no_reg_met)
                                    <br>{{ $aesor->no_reg_met }}
                                    @endif
                                    @php $lastAsesorId = $asesorId; @endphp
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
