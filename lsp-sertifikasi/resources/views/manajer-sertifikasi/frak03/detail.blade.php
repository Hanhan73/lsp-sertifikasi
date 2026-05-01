{{-- resources/views/manajer-sertifikasi/frak03/detail.blade.php --}}
@extends('layouts.manajer-sertifikasi')

@section('title', 'FR.AK.03 - Detail Jadwal')

@section('content')
<div class="container-fluid py-4">

    {{-- Back + Header --}}
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('manajer-sertifikasi.frak03.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0">FR.AK.03 — {{ $schedule->skema->name ?? '-' }}</h5>
            <p class="text-muted small mb-0">
                {{ $schedule->assessment_date?->translatedFormat('d F Y') }} •
                {{ $schedule->tuk->name ?? '-' }} •
                Asesor: {{ $schedule->asesor->nama ?? '-' }}
            </p>
        </div>
    </div>

    {{-- Statistik --}}
    @php
        $total     = $schedule->asesmens->count();
        $submitted = $schedule->asesmens->filter(fn($a) => $a->frAk03 && $a->frAk03->isSubmitted())->count();
        $belum     = $total - $submitted;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-primary text-white text-center py-3">
                <div class="fs-2 fw-bold">{{ $total }}</div>
                <div class="small">Total Asesi</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-success text-white text-center py-3">
                <div class="fs-2 fw-bold">{{ $submitted }}</div>
                <div class="small">Sudah Isi</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-warning text-white text-center py-3">
                <div class="fs-2 fw-bold">{{ $belum }}</div>
                <div class="small">Belum Isi</div>
            </div>
        </div>
    </div>

    {{-- Tabel asesi --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-semibold">Daftar Asesi</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">No</th>
                        <th>Nama Asesi</th>
                        <th>Institusi</th>
                        <th class="text-center">Status FR.AK.03</th>
                        <th class="text-center">Waktu Submit</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule->asesmens as $i => $asesmen)
                        <tr>
                            <td class="align-middle text-muted">{{ $i + 1 }}</td>
                            <td class="align-middle fw-semibold">{{ $asesmen->full_name }}</td>
                            <td class="align-middle small text-muted">{{ $asesmen->institution ?? '-' }}</td>
                            <td class="text-center align-middle">
                                @if($asesmen->frAk03 && $asesmen->frAk03->isSubmitted())
                                    <span class="badge bg-success">Sudah Diisi</span>
                                @else
                                    <span class="badge bg-secondary">Belum Diisi</span>
                                @endif
                            </td>
                            <td class="text-center align-middle small">
                                {{ $asesmen->frAk03?->submitted_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="text-center align-middle">
                                @if($asesmen->frAk03 && $asesmen->frAk03->isSubmitted())
                                    <a href="{{ route('manajer-sertifikasi.frak03.pdf', [$schedule, $asesmen]) }}?preview=1"
                                       class="btn btn-sm btn-outline-danger" target="_blank">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Preview PDF
                                    </a>
                                    <a href="{{ route('manajer-sertifikasi.frak03.pdf', [$schedule, $asesmen]) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download me-1"></i> Unduh
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection