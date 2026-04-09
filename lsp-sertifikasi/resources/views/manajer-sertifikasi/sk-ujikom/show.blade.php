@extends('layouts.app')
@section('title', 'Detail SK Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom › Detail')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <a href="{{ route('manajer-sertifikasi.sk-ujikom.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Detail Pengajuan SK</h4>
            <p class="text-muted mb-0" style="font-size:.875rem;">
                Batch: <code>{{ $skUjikom->collective_batch_id }}</code> &nbsp;·&nbsp;
                {{ $first?->skema?->name ?? '-' }}
            </p>
        </div>
        @if($skUjikom->isApproved() && $skUjikom->hasSk())
        <a href="{{ route('manajer-sertifikasi.sk-ujikom.download', $skUjikom) }}"
           class="btn btn-success">
            <i class="bi bi-download me-2"></i>Unduh SK PDF
        </a>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
</div>
@endif

{{-- Status Banner --}}
<div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-3
    {{ $skUjikom->isApproved() ? 'alert-success' : ($skUjikom->isRejected() ? 'alert-danger' : 'alert-warning') }}">
    <i class="bi bi-{{ $skUjikom->isApproved() ? 'check-circle-fill' : ($skUjikom->isRejected() ? 'x-circle-fill' : 'hourglass-split') }} fs-4"></i>
    <div>
        <div class="fw-semibold">{{ $skUjikom->status_label }}</div>
        @if($skUjikom->isSubmitted())
        <div class="small">Dikirim {{ $skUjikom->submitted_at?->translatedFormat('d M Y H:i') }}</div>
        @elseif($skUjikom->isApproved())
        <div class="small">Disetujui oleh {{ $skUjikom->approvedBy?->name ?? '-' }} pada {{ $skUjikom->approved_at?->translatedFormat('d M Y H:i') }}</div>
        @elseif($skUjikom->isRejected())
        <div class="small mt-1"><strong>Alasan:</strong> {{ $skUjikom->catatan_direktur }}</div>
        @endif
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
                        <td class="text-muted">Status</td>
                        <td><span class="badge bg-{{ $skUjikom->status_badge }}">{{ $skUjikom->status_label }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Diajukan</td>
                        <td class="small">{{ $skUjikom->submitted_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
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
                @php $ba = $s->beritaAcara; @endphp
                <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between" style="font-size:.83rem;">
                    <div>
                        <div class="fw-semibold">{{ $s->assessment_date->translatedFormat('d M Y') }}</div>
                        <div class="text-muted">{{ $s->asesor?->nama ?? '-' }}</div>
                    </div>
                    @if($ba)
                    <div class="d-flex gap-1">
                        <a href="{{ route('manajer-sertifikasi.jadwal.berita-acara.pdf', $s) }}?preview=1"
                           target="_blank"
                           class="btn btn-outline-danger btn-sm py-0 px-2"
                           title="Lihat PDF BA">
                            <i class="bi bi-file-pdf" style="font-size:.8rem;"></i>
                        </a>
                        @if($ba->file_path)
                        <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-ba', $s) }}"
                           class="btn btn-outline-secondary btn-sm py-0 px-2"
                           title="Download Excel BA">
                            <i class="bi bi-file-earmark-spreadsheet" style="font-size:.8rem;"></i>
                        </a>
                        @endif
                    </div>
                    @else
                    <span class="badge bg-secondary" style="font-size:.7rem;">BA -</span>
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
                            <th>Instansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesertaKompeten as $i => $asesi)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}.</td>
                            <td class="fw-semibold">{{ $asesi->full_name }}</td>
                            <td class="text-muted">{{ $asesi->institution ?? $first?->tuk?->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection