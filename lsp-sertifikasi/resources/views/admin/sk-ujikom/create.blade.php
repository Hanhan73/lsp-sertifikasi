@extends('layouts.app')
@section('title', 'Generate SK Hasil Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom › Generate')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.sk-ujikom.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h4 class="fw-bold mb-1">Generate SK Hasil Ujikom</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
        Batch: <code>{{ $batchId }}</code> &nbsp;·&nbsp;
        {{ $first->skema?->name ?? '-' }} &nbsp;·&nbsp;
        {{ $first->tuk?->name ?? '-' }}
    </p>
</div>

<div class="row g-4">

    {{-- ── KIRI: Form ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Data SK
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sk-ujikom.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="collective_batch_id" value="{{ $batchId }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor SK <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_sk"
                               class="form-control @error('nomor_sk') is-invalid @enderror"
                               value="{{ old('nomor_sk') }}"
                               placeholder="cth: 001/LSP-KAP/SER.10.08/IV/2026"
                               required>
                        @error('nomor_sk')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: 001/LSP-KAP/SER.10.08/BulanRomawi/Tahun</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Pleno <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pleno"
                               class="form-control @error('tanggal_pleno') is-invalid @enderror"
                               value="{{ old('tanggal_pleno') }}"
                               required>
                        @error('tanggal_pleno')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tanggal rapat pleno panitia teknis ujian kompetensi.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Dikeluarkan di <span class="text-danger">*</span></label>
                        <input type="text" name="tempat_dikeluarkan"
                               class="form-control @error('tempat_dikeluarkan') is-invalid @enderror"
                               value="{{ old('tempat_dikeluarkan', 'Bandung') }}"
                               required>
                        @error('tempat_dikeluarkan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-success border-0 py-2 mb-3" style="font-size:.85rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        SK akan langsung digenerate dan berstatus <strong>Disetujui</strong> setelah disimpan.
                        PDF akan otomatis dibuat.
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-file-earmark-check me-2"></i>Generate & Setujui SK
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── KANAN: Preview ── --}}
    <div class="col-lg-7">

        {{-- Jadwal dalam batch --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar3 me-2 text-secondary"></i>Jadwal dalam Batch
                <span class="badge bg-light text-dark border ms-2">{{ $schedules->count() }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle" style="font-size:.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>TUK</th>
                            <th>Asesor</th>
                            <th class="text-center">BA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $s)
                        <tr>
                            <td class="ps-3">{{ $s->assessment_date->translatedFormat('d M Y') }}</td>
                            <td>{{ $s->tuk?->name ?? '-' }}</td>
                            <td>
                                {{ $s->asesor?->nama ?? '-' }}
                                @if($s->asesor?->no_reg_met)
                                <br><small class="text-muted">{{ $s->asesor->no_reg_met }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->beritaAcara)
                                <span class="badge bg-success" style="font-size:.7rem;">Ada</span>
                                @else
                                <span class="badge bg-secondary" style="font-size:.7rem;">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Peserta Kompeten --}}
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
                                    <br><span class="text-muted">{{ $asesor->no_reg_met }}</span>
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
