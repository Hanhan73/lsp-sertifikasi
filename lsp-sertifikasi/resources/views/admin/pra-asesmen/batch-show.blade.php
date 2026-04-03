@extends('layouts.app')
@section('title', 'Detail Batch - ' . $batchId)
@section('page-title', 'Review Batch Kolektif')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">

    {{-- Kiri: Info Batch & Daftar Peserta --}}
    <div class="col-lg-8">

        {{-- Info Batch --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Batch
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="130">Batch ID</td>
                                <td>: <code>{{ $batchId }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">TUK</td>
                                <td>: {{ $firstBatch->tuk->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Skema</td>
                                <td>: {{ $firstBatch->skema->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="130">Total Peserta</td>
                                <td>: <strong>{{ $asesmens->count() }} orang</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tgl Daftar</td>
                                <td>: {{ $firstBatch->registration_date->translatedFormat('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Didaftarkan oleh</td>
                                <td>: {{ $firstBatch->registrar->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Peserta --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-people me-2 text-primary"></i>Daftar Peserta
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th class="text-center">Dokumen</th>
                                <th class="text-center">Status Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $i => $asesmen)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                                    <div class="text-muted small">{{ $asesmen->nik ?? 'NIK belum diisi' }}</div>
                                </td>
                                <td class="small">{{ $asesmen->user->email }}</td>
                                <td class="text-center">
                                    @if($asesmen->photo_path && $asesmen->ktp_path && $asesmen->document_path)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Lengkap
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-circle me-1"></i>Belum Lengkap
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $asesmen->status_badge }}">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Kanan: Mulai Asesmen --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="top:80px;">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-play-circle me-2"></i>Mulai Asesmen Batch
            </div>
            <div class="card-body">

                {{-- Ringkasan status dokumen --}}
                @php
                    $totalPeserta  = $asesmens->count();
                    $dokumenLengkap = $asesmens->filter(fn($a) =>
                        $a->photo_path && $a->ktp_path && $a->document_path
                    )->count();
                    $allReady = $dokumenLengkap === $totalPeserta;
                @endphp

                <div class="mb-4">
                    <p class="small fw-semibold text-muted mb-2">KELENGKAPAN DOKUMEN</p>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Dokumen lengkap</span>
                        <span class="fw-semibold small">{{ $dokumenLengkap }}/{{ $totalPeserta }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        <div class="progress-bar {{ $allReady ? 'bg-success' : 'bg-warning' }}"
                            style="width: {{ $totalPeserta > 0 ? round($dokumenLengkap / $totalPeserta * 100) : 0 }}%">
                        </div>
                    </div>

                    @foreach([
                        ['label' => 'Total peserta',      'value' => $totalPeserta . ' orang'],
                        ['label' => 'Dokumen lengkap',    'value' => $dokumenLengkap . ' orang'],
                        ['label' => 'Belum lengkap',      'value' => ($totalPeserta - $dokumenLengkap) . ' orang'],
                    ] as $row)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="small text-muted">{{ $row['label'] }}</span>
                        <span class="small fw-semibold">{{ $row['value'] }}</span>
                    </div>
                    @endforeach
                </div>

                @if(!$allReady)
                <div class="alert alert-warning py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <small>
                        <strong>{{ $totalPeserta - $dokumenLengkap }} peserta</strong> belum melengkapi dokumen.
                        Anda tetap bisa memulai asesmen.
                    </small>
                </div>
                @endif

                <form action="{{ route('admin.praasesmen.batch.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batchId }}">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan (opsional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3"
                            placeholder="Catatan untuk batch ini..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Mulai asesmen untuk {{ $totalPeserta }} peserta dalam batch {{ $batchId }}?')">
                            <i class="bi bi-play-circle me-1"></i>
                            Mulai Asesmen ({{ $totalPeserta }} peserta)
                        </button>
                        <a href="{{ route('admin.praasesmen.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection