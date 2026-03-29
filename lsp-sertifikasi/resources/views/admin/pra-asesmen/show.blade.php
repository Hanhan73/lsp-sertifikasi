@extends('layouts.app')
@section('title', 'Detail Asesi - Mulai Asesmen')
@section('page-title', 'Review Data Asesi')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    {{-- Kiri: Data asesi --}}
    <div class="col-lg-8">

        {{-- Info Pendaftaran --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Pendaftaran
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><td class="text-muted" width="140">No. Registrasi</td><td>: <strong>#{{ $asesmen->id }}</strong></td></tr>
                            <tr><td class="text-muted">Tgl Daftar</td><td>: {{ $asesmen->registration_date->format('d F Y') }}</td></tr>
                            <tr><td class="text-muted">Jenis</td><td>: <span class="badge bg-success">Mandiri</span></td></tr>
                            <tr><td class="text-muted">Skema</td><td>: {{ $asesmen->skema->name ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><td class="text-muted" width="140">TUK</td><td>: {{ $asesmen->tuk->name ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Tanggal Pilihan</td><td>: {{ $asesmen->preferred_date?->format('d F Y') ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Pelatihan</td>
                                <td>: @if($asesmen->training_flag)
                                    <span class="badge bg-warning text-dark">Ya</span>
                                @else
                                    <span class="badge bg-secondary">Tidak</span>
                                @endif</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Pribadi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person me-2 text-primary"></i>Data Pribadi
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><td class="text-muted" width="140">Nama Lengkap</td><td>: <strong>{{ $asesmen->full_name }}</strong></td></tr>
                            <tr><td class="text-muted">NIK</td><td>: <code>{{ $asesmen->nik }}</code></td></tr>
                            <tr><td class="text-muted">Tempat Lahir</td><td>: {{ $asesmen->birth_place }}</td></tr>
                            <tr><td class="text-muted">Tgl Lahir</td><td>: {{ $asesmen->birth_date->format('d F Y') }}</td></tr>
                            <tr><td class="text-muted">Jenis Kelamin</td><td>: {{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                            <tr><td class="text-muted">No. HP</td><td>: {{ $asesmen->phone }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><td class="text-muted" width="140">Alamat</td><td>: {{ $asesmen->address }}</td></tr>
                            <tr><td class="text-muted">Pendidikan</td><td>: {{ $asesmen->education }}</td></tr>
                            <tr><td class="text-muted">Pekerjaan</td><td>: {{ $asesmen->occupation }}</td></tr>
                            <tr><td class="text-muted">Sumber Anggaran</td><td>: {{ $asesmen->budget_source }}</td></tr>
                            <tr><td class="text-muted">Asal Lembaga</td><td>: {{ $asesmen->institution }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dokumen --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark me-2 text-primary"></i>Dokumen
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    @foreach([
                        ['label' => 'Pas Foto', 'path' => $asesmen->photo_path, 'type' => 'image'],
                        ['label' => 'KTP',      'path' => $asesmen->ktp_path,   'type' => 'pdf'],
                        ['label' => 'Ijazah/Transkrip', 'path' => $asesmen->document_path, 'type' => 'pdf'],
                    ] as $doc)
                    <div class="col-md-4">
                        <div class="card h-100 border">
                            <div class="card-header bg-light small fw-semibold">{{ $doc['label'] }}</div>
                            <div class="card-body">
                                @if($doc['path'])
                                    @if($doc['type'] === 'image')
                                        <img src="{{ asset('storage/' . $doc['path']) }}"
                                            class="img-fluid rounded mb-2" style="max-height:160px;object-fit:cover;">
                                    @else
                                        <div class="py-3">
                                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size:3rem;"></i>
                                        </div>
                                    @endif
                                    <a href="{{ asset('storage/' . $doc['path']) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                @else
                                    <div class="py-4 text-muted">
                                        <i class="bi bi-exclamation-circle text-warning" style="font-size:2rem;"></i>
                                        <p class="small mt-2 mb-0">Belum diupload</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Kanan: Form Mulai Asesmen --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-play-circle me-2"></i>Mulai Asesmen
            </div>
            <div class="card-body">

                {{-- Checklist dokumen --}}
                <div class="mb-4">
                    <p class="small fw-semibold text-muted mb-2">STATUS DOKUMEN</p>
                    @foreach([
                        ['label' => 'Pas Foto',         'ok' => (bool) $asesmen->photo_path],
                        ['label' => 'KTP',              'ok' => (bool) $asesmen->ktp_path],
                        ['label' => 'Ijazah/Transkrip', 'ok' => (bool) $asesmen->document_path],
                        ['label' => 'NIK terisi',       'ok' => (bool) $asesmen->nik],
                        ['label' => 'Skema dipilih',    'ok' => (bool) $asesmen->skema_id],
                    ] as $check)
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="small">{{ $check['label'] }}</span>
                        @if($check['ok'])
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-x-circle-fill text-danger"></i>
                        @endif
                    </div>
                    @endforeach
                </div>

                @php
                    $allReady = $asesmen->photo_path && $asesmen->ktp_path
                        && $asesmen->document_path && $asesmen->nik && $asesmen->skema_id;
                @endphp

                @if(!$allReady)
                <div class="alert alert-warning py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <small>Beberapa data belum lengkap. Anda tetap bisa memulai asesmen.</small>
                </div>
                @endif

                <form action="{{ route('admin.praasesmen.process', $asesmen) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan (opsional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3"
                            placeholder="Catatan untuk asesi ini..."></textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-circle me-1"></i> Mulai Asesmen
                        </button>
                        <a href="{{ route('admin.praasesmen.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection