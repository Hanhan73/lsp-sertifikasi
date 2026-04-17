@extends('layouts.app')
@section('title', 'Detail Asesi — ' . $asesmen->full_name)
@section('page-title', 'Detail Asesi')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
/* ── Design system ──────────────────────────────────────── */
.section-heading {
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 12px;
}
.info-row { display: flex; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f1f5f9; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #94a3b8; font-size: .82rem; min-width: 160px; flex-shrink: 0; }
.info-value { font-weight: 600; font-size: .9rem; }

/* ── TTD ────────────────────────────────────────────────── */
.ttd-thumb {
    max-height: 70px; max-width: 180px; object-fit: contain;
    border: 1px solid #e2e8f0; border-radius: 4px; background: #fff; padding: 4px;
}

/* ── Bukti cards ────────────────────────────────────────── */
.bukti-card { border-left: 4px solid #e2e8f0; border-radius: 0 6px 6px 0; }
.bukti-card.ok   { border-left-color: #22c55e; }
.bukti-card.warn { border-left-color: #f59e0b; }

/* ── Batch table ────────────────────────────────────────── */
.batch-member-row { cursor: pointer; }
.batch-member-row:hover { background: #f8faff; }

/* ── APL-02 elemen rows ─────────────────────────────────── */
.elemen-row { border-bottom: 1px solid #f1f5f9; padding: 10px 16px; }
.elemen-row:last-child { border-bottom: none; }
.elemen-row.answered-K  { border-left: 3px solid #10b981; }
.elemen-row.answered-BK { border-left: 3px solid #ef4444; }
.elemen-row.unanswered  { border-left: 3px solid #e2e8f0; }
.ro-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: .78rem; font-weight: 700; }
.ro-badge-K  { background: #d1fae5; color: #065f46; }
.ro-badge-BK { background: #fee2e2; color: #991b1b; }

/* ── Unit accordion (APL-02 & AK.01) ───────────────────── */
.unit-card { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 10px; }
.unit-header {
    background: #f8fafc; padding: 10px 14px; cursor: pointer;
    user-select: none; border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; gap: 10px;
}
.unit-header:hover { background: #f1f5f9; }

/* ── FR.AK.04 banding ───────────────────────────────────── */
.pertanyaan-card {
    border: 1px solid #e2e8f0; border-radius: 8px;
    padding: 12px 16px; margin-bottom: 8px; background: #fff;
}
.pertanyaan-card.answered { border-color: #3b82f6; background: #f0f7ff; }

/* ── Status bar helper ──────────────────────────────────── */
.status-bar-submitted { background: #eff6ff; border: 1px solid #bfdbfe; }
.status-bar-verified  { background: #f0fdf4; border: 1px solid #bbf7d0; }
.status-bar-default   { background: #f8fafc; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')

{{-- ── Breadcrumb ─────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.asesi') }}">Semua Asesi</a></li>
        @if($asesmen->is_collective && $asesmen->collective_batch_id)
        <li class="breadcrumb-item text-muted">Batch {{ $asesmen->collective_batch_id }}</li>
        @endif
        <li class="breadcrumb-item active">{{ $asesmen->full_name }}</li>
    </ol>
</nav>

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — Header Cards
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Identitas Asesi --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex gap-3 align-items-start">
                @if($asesmen->photo_path)
                <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                     class="rounded" style="width:72px;height:90px;object-fit:cover;flex-shrink:0;">
                @else
                <div class="rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:72px;height:90px;">
                    <i class="bi bi-person-fill text-muted fs-2"></i>
                </div>
                @endif
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">{{ $asesmen->full_name }}</h5>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-{{ $asesmen->status_badge }} fs-6">{{ $asesmen->status_label }}</span>
                        @if($asesmen->is_collective)
                        <span class="badge bg-primary"><i class="bi bi-people me-1"></i>Kolektif</span>
                        @else
                        <span class="badge bg-success"><i class="bi bi-person me-1"></i>Mandiri</span>
                        @endif
                    </div>
                    <div class="small text-muted">
                        <div><i class="bi bi-credit-card me-1"></i><span class="font-monospace">{{ $asesmen->nik ?? '-' }}</span></div>
                        <div><i class="bi bi-telephone me-1"></i>{{ $asesmen->phone ?? '-' }}</div>
                    </div>
                    <div class="card border-warning mt-3">
                        <div class="card-header bg-warning bg-opacity-10 py-2">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-envelope-at me-1"></i> Ganti Email Login
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <p class="text-muted small mb-3">
                                Email saat ini: <strong>{{ $asesmen->user->email }}</strong>
                                @if(!$asesmen->user->email_verified_at)
                                    <span class="badge bg-warning text-dark ms-1">Belum Terverifikasi</span>
                                @else
                                    <span class="badge bg-success ms-1">Terverifikasi</span>
                                @endif
                            </p>
                            <div class="input-group">
                                <input type="email" id="inputEmailBaru" class="form-control form-control-sm"
                                    placeholder="Email baru asesi..."
                                    value="{{ $asesmen->user->email }}">
                                <button class="btn btn-warning btn-sm" onclick="gantiEmailAsesi({{ $asesmen->id }})">
                                    <i class="bi bi-check-lg me-1"></i>Simpan
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Setelah diganti, status verifikasi email akan direset dan asesi perlu verifikasi ulang.
                            </div>

                            <hr class="my-2">
                            <button class="btn btn-outline-danger btn-sm w-100"
                                onclick="resetPasswordAsesi({{ $asesmen->id }})">
                                <i class="bi bi-key me-1"></i>Reset Password ke "password123"
                            </button>
                            {{-- Impersonate --}}
                            <form action="{{ route('admin.asesi.impersonate', $asesmen) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm"
                                    onclick="return confirm('Masuk sebagai {{ $asesmen->user->name }}?')">
                                    <i class="bi bi-person-fill-gear"></i> Login sebagai Asesi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </div>

    {{-- Info Asesmen --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-award me-1"></i>Info Asesmen</div>
                <div class="info-row"><span class="info-label">Skema</span><span class="info-value">{{ $asesmen->skema->name ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">TUK</span><span class="info-value">{{ $asesmen->tuk->name ?? '-' }}</span></div>
                <div class="info-row">
                    <span class="info-label">Jadwal</span>
                    <span class="info-value">
                        {{ $asesmen->schedule?->assessment_date?->translatedFormat('d F Y') ?? '-' }}
                        @if($asesmen->schedule?->start_time)
                        <span class="text-muted small ms-1">{{ $asesmen->schedule->start_time }}</span>
                        @endif
                    </span>
                </div>
                <div class="info-row"><span class="info-label">Asesor</span><span class="info-value">{{ $asesmen->schedule?->asesor?->nama ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Tgl Daftar</span><span class="info-value">{{ $asesmen->registration_date->translatedFormat('d M Y') }}</span></div>
                @if($asesmen->is_collective)
                <div class="info-row"><span class="info-label">Didaftarkan oleh</span><span class="info-value">{{ $asesmen->registrar->name ?? '-' }}</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Status Dokumen --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-files me-1"></i>Status Dokumen</div>
                @foreach([
                    ['label' => 'APL-01',   'model' => $asesmen->aplsatu],
                    ['label' => 'APL-02',   'model' => $asesmen->apldua],
                    ['label' => 'FR.AK.01', 'model' => $asesmen->frak01],
                    ['label' => 'FR.AK.04', 'model' => $asesmen->frak04],
                ] as $doc)
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span class="small fw-semibold">{{ $doc['label'] }}</span>
                    <span class="badge bg-{{ $doc['model']?->status_badge ?? 'light border' }}">
                        {{ $doc['model']?->status_label ?? '-' }}
                    </span>
                </div>
                @endforeach
                @if($asesmen->result)
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">Hasil Asesmen</span>
                    <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                        {{ ucfirst($asesmen->result) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     BATCH INFO
══════════════════════════════════════════════════════════ --}}
@if($asesmen->is_collective && $batchMembers?->count() > 1)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">
            <i class="bi bi-people-fill me-2 text-primary"></i>
            Batch Kolektif — <code>{{ $asesmen->collective_batch_id }}</code>
            <span class="badge bg-primary ms-2">{{ $batchMembers->count() }} peserta</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="button"
                data-bs-toggle="collapse" data-bs-target="#batchTable">
            <i class="bi bi-chevron-down"></i> Tampilkan
        </button>
    </div>
    <div class="collapse" id="batchTable">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light" style="font-size:.78rem;">
                    <tr>
                        <th class="ps-3">#</th><th>Nama</th><th>Status</th>
                        <th class="text-center">APL-01</th><th class="text-center">APL-02</th>
                        <th class="text-center">AK.01</th><th class="text-center">AK.04</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batchMembers as $i => $member)
                    <tr class="batch-member-row {{ $member->id === $asesmen->id ? 'table-primary' : '' }}"
                        onclick="window.location='{{ route('admin.asesi.show', $member) }}'">
                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                        <td>
                            <span class="fw-semibold">{{ $member->full_name }}</span>
                            @if($member->id === $asesmen->id)
                            <span class="badge bg-primary ms-1" style="font-size:.65rem;">Sedang dilihat</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $member->status_badge }}">{{ $member->status_label }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->aplsatu?->status_badge ?? 'light border' }}">{{ $member->aplsatu?->status_label ?? '-' }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->apldua?->status_badge ?? 'light border' }}">{{ $member->apldua?->status_label ?? '-' }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->frak01?->status_badge ?? 'light border' }}">{{ $member->frak01?->status_label ?? '-' }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->frak04?->status_badge ?? 'light border' }}">{{ $member->frak04?->status_label ?? '-' }}</span></td>
                        <td class="pe-2">@if($member->id !== $asesmen->id)<i class="bi bi-arrow-right text-muted small"></i>@endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     TABS UTAMA
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom pt-0 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="docTabs">
            <li class="nav-item">
                <button class="nav-link active py-3" data-bs-toggle="tab" data-bs-target="#tab-biodata">
                    <i class="bi bi-person me-1"></i>Biodata
                    @if($asesmen->biodata_needs_revision)
                        <span class="badge bg-danger ms-1" style="font-size:.6rem;">Perlu Revisi</span>
                    @elseif(!$asesmen->biodata_verified_at && in_array($asesmen->status, ['pra_asesmen_started','scheduled','pra_asesmen_completed','assessed']))
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Perlu Verif</span>
                    @elseif($asesmen->biodata_verified_at)
                        <span class="badge bg-success ms-1" style="font-size:.6rem;">✓</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-apl01">
                    <i class="bi bi-file-earmark-person me-1"></i>APL-01
                    @if($asesmen->aplsatu?->status === 'submitted')
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Perlu Verif</span>
                    @elseif($asesmen->aplsatu?->status === 'verified')
                    <span class="badge bg-success ms-1" style="font-size:.6rem;">✓</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-apl02">
                    <i class="bi bi-clipboard-check me-1"></i>APL-02
                    @if($asesmen->apldua?->status === 'submitted')
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Perlu Verif</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-frak01">
                    <i class="bi bi-file-earmark-check me-1"></i>FR.AK.01
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-frak04">
                    <i class="bi bi-megaphone me-1"></i>FR.AK.04
                    @if($asesmen->frak04?->status === 'submitted')
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Banding!</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">

            {{-- ════════════════════════════════════════════
                 TAB 1 — BIODATA (lengkap, dari pasted-2/doc-4)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="tab-biodata">
                <div class="row g-4 pt-2">

                    {{-- Kolom kiri: Data pribadi lengkap --}}
                    <div class="col-md-7">

                        {{-- Informasi Pendaftaran --}}
                        <div class="section-heading">Informasi Pendaftaran</div>
                        <div class="info-row"><span class="info-label">No. Registrasi</span><span class="info-value font-monospace">#{{ $asesmen->id }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Daftar</span><span class="info-value">{{ $asesmen->registration_date->translatedFormat('d F Y') }}</span></div>
                        <div class="info-row">
                            <span class="info-label">Tipe Pendaftaran</span>
                            <span class="info-value">
                                @if($asesmen->is_collective)
                                <span class="badge bg-primary">Kolektif</span>
                                @else
                                <span class="badge bg-secondary">Mandiri</span>
                                @endif
                            </span>
                        </div>
                        @if($asesmen->is_collective)
                        <div class="info-row"><span class="info-label">Batch ID</span><span class="info-value font-monospace">{{ $asesmen->collective_batch_id }}</span></div>
                        <div class="info-row">
                            <span class="info-label">Waktu Bayar</span>
                            <span class="info-value">
                                <span class="badge bg-{{ $asesmen->collective_payment_timing === 'before' ? 'warning text-dark' : 'success' }}">
                                    {{ $asesmen->collective_payment_timing === 'before' ? 'Sebelum Asesmen' : 'Setelah Asesmen' }}
                                </span>
                            </span>
                        </div>
                        <div class="info-row"><span class="info-label">Didaftarkan Oleh</span><span class="info-value">{{ $asesmen->registrar->name ?? '-' }}</span></div>
                        @endif
                        <div class="info-row"><span class="info-label">Skema</span><span class="info-value">{{ $asesmen->skema->name ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Skema</span><span class="info-value font-monospace small">{{ $asesmen->skema->nomor_skema ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Pilihan</span><span class="info-value">{{ $asesmen->preferred_date ? $asesmen->preferred_date->translatedFormat('d F Y') : '-' }}</span></div>

                        {{-- Data Pribadi --}}
                        <div class="section-heading mt-4">Data Pribadi</div>
                        <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value">{{ $asesmen->full_name }}</span></div>
                        <div class="info-row"><span class="info-label">NIK</span><span class="info-value font-monospace">{{ $asesmen->nik }}</span></div>
                        <div class="info-row"><span class="info-label">Tempat Lahir</span><span class="info-value">{{ $asesmen->birth_place }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Lahir</span><span class="info-value">{{ $asesmen->birth_date ? $asesmen->birth_date->translatedFormat('d F Y') : '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Jenis Kelamin</span><span class="info-value">{{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                        <div class="info-row"><span class="info-label">Email</span><span class="info-value">{{ $asesmen->email ?? $asesmen->user->email ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Telepon</span><span class="info-value">{{ $asesmen->phone }}</span></div>
                        <div class="info-row"><span class="info-label">Alamat</span><span class="info-value">{{ $asesmen->address }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Provinsi</span><span class="info-value">{{ $asesmen->province_code ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Kota</span><span class="info-value">{{ $asesmen->city_code ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Pendidikan</span><span class="info-value">{{ $asesmen->education ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Pekerjaan</span><span class="info-value">{{ $asesmen->occupation ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Asal Lembaga</span><span class="info-value">{{ $asesmen->institution ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Sumber Anggaran</span><span class="info-value">{{ $asesmen->budget_source ?? '-' }}</span></div>
                    </div>

                    {{-- Kolom kanan: Dokumen pendaftaran --}}
                    <div class="col-md-5">
                        <div class="section-heading">Dokumen Pendaftaran</div>

                        {{-- Pas Foto --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 small fw-semibold">Pas Foto</div>
                            <div class="card-body text-center p-2">
                                @if($asesmen->photo_path)
                                <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                                     alt="Foto" class="img-thumbnail mb-2"
                                     style="max-height:200px; object-fit:cover;">
                                <div>
                                    <a href="{{ asset('storage/' . $asesmen->photo_path) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                </div>
                                @else
                                <div class="py-3 text-muted small">
                                    <i class="bi bi-x-circle text-danger d-block fs-3 mb-1"></i>Belum upload
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- KTP --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 small fw-semibold">KTP</div>
                            <div class="card-body text-center p-2">
                                @if($asesmen->ktp_path)
                                <iframe src="{{ asset('storage/' . $asesmen->ktp_path) }}"
                                        style="width:100%; height:180px; border:1px solid #ddd;"
                                        class="mb-2"></iframe>
                                <div>
                                    <a href="{{ asset('storage/' . $asesmen->ktp_path) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                                @else
                                <div class="py-3 text-muted small">
                                    <i class="bi bi-x-circle text-danger d-block fs-3 mb-1"></i>Belum upload
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Ijazah --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 small fw-semibold">Ijazah / Transkrip</div>
                            <div class="card-body text-center p-2">
                                @if($asesmen->document_path)
                                <iframe src="{{ asset('storage/' . $asesmen->document_path) }}"
                                        style="width:100%; height:180px; border:1px solid #ddd;"
                                        class="mb-2"></iframe>
                                <div>
                                    <a href="{{ asset('storage/' . $asesmen->document_path) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                                @else
                                <div class="py-3 text-muted small">
                                    <i class="bi bi-x-circle text-danger d-block fs-3 mb-1"></i>Belum upload
                                </div>
                                @endif
                            </div>
                        </div>
                        @include('admin.asesmen.partials.biodata-reject-panel')
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 2 — APL-01 (read-only dari doc-5)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-apl01">
                @if(!$asesmen->aplsatu)
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                    <p class="mt-2">APL-01 belum diisi oleh asesi.</p>
                </div>
                @else
                @php $apl = $asesmen->aplsatu; @endphp

                {{-- Status bar --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded
                    {{ $apl->status === 'verified' ? 'status-bar-verified' : ($apl->status === 'submitted' ? 'status-bar-submitted' : 'status-bar-default') }}">
                    <span class="badge bg-{{ $apl->status_badge }} fs-6">{{ $apl->status_label }}</span>
                    @if($apl->submitted_at)
                    <span class="small text-muted">Submit: {{ $apl->submitted_at->translatedFormat('d M Y H:i') }}</span>
                    @endif
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('admin.apl01.pdf', [$apl, 'preview' => 1]) }}" target="_blank"
                        class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-pdf me-1"></i>Preview PDF
                        </a>
                        <a href="{{ route('admin.apl01.pdf', $apl) }}"
                        class="btn btn-sm btn-success">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                        {{-- HAPUS tombol kuning "Verifikasi" yang redirect ke page lain --}}
                        @if($apl->status === 'submitted')
                        <button class="btn btn-sm btn-warning text-dark" onclick="bukaModalVerifikasiApl()">
                            <i class="bi bi-pen me-1"></i>Verifikasi
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="openRejectApl01Modal()">
                            <i class="bi bi-arrow-return-left me-1"></i>Kembalikan
                        </button>
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Data Pribadi --}}
                    <div class="col-md-6">
                        <div class="section-heading">a. Data Pribadi</div>
                        <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value">{{ $apl->nama_lengkap }}</span></div>
                        <div class="info-row"><span class="info-label">NIK</span><span class="info-value font-monospace">{{ $apl->nik }}</span></div>
                        <div class="info-row"><span class="info-label">Tempat Lahir</span><span class="info-value">{{ $apl->tempat_lahir }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Lahir</span><span class="info-value">{{ $apl->tanggal_lahir?->translatedFormat('d M Y') ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Jenis Kelamin</span><span class="info-value">{{ $apl->jenis_kelamin }}</span></div>
                        <div class="info-row"><span class="info-label">Kebangsaan</span><span class="info-value">{{ $apl->kebangsaan ?? 'Indonesia' }}</span></div>
                        <div class="info-row"><span class="info-label">Kualifikasi Pendidikan</span><span class="info-value">{{ $apl->kualifikasi_pendidikan ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Alamat Rumah</span><span class="info-value">{{ $apl->alamat_rumah }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Pos</span><span class="info-value">{{ $apl->kode_pos ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Telepon Rumah</span><span class="info-value">{{ $apl->telp_rumah ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">No. HP</span><span class="info-value">{{ $apl->hp }}</span></div>
                        <div class="info-row"><span class="info-label">Email</span><span class="info-value">{{ $apl->email }}</span></div>

                        <div class="section-heading mt-4">b. Data Pekerjaan</div>
                        <div class="info-row"><span class="info-label">Institusi / Perusahaan</span><span class="info-value">{{ $apl->nama_institusi ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Jabatan</span><span class="info-value">{{ $apl->jabatan ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Alamat Kantor</span><span class="info-value">{{ $apl->alamat_kantor ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Pos Kantor</span><span class="info-value">{{ $apl->kode_pos_kantor ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Telepon Kantor</span><span class="info-value">{{ $apl->telp_kantor_detail ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Fax</span><span class="info-value">{{ $apl->fax_kantor ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Email Kantor</span><span class="info-value">{{ $apl->email_kantor ?? '-' }}</span></div>

                        <div class="section-heading mt-4">c. Data Sertifikasi</div>
                        <div class="info-row"><span class="info-label">Skema</span><span class="info-value fw-bold">{{ $apl->asesmen?->skema?->name ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Nomor Skema</span><span class="info-value font-monospace small">{{ $apl->asesmen?->skema?->nomor_skema ?? ($apl->asesmen?->skema?->code ?? '-') }}</span></div>
                        <div class="info-row">
                            <span class="info-label">Tujuan Asesmen</span>
                            <span class="info-value">
                                {{ $apl->tujuan_asesmen ?? '-' }}
                                @if($apl->tujuan_asesmen === 'Lainnya' && $apl->tujuan_asesmen_lainnya)
                                — {{ $apl->tujuan_asesmen_lainnya }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Unit Kompetensi + TTD + Bukti --}}
                    <div class="col-md-6">

                        {{-- Unit Kompetensi --}}
                        @if($apl->asesmen?->skema?->unitKompetensis?->isNotEmpty())
                        <div class="section-heading">Unit Kompetensi</div>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered" style="font-size:.82rem;">
                                <thead class="table-light">
                                    <tr><th width="30" class="text-center">No</th><th width="140">Kode Unit</th><th>Judul Unit</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($apl->asesmen->skema->unitKompetensis as $i => $unit)
                                    <tr>
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td><small class="font-monospace">{{ $unit->kode_unit }}</small></td>
                                        <td>{{ $unit->judul_unit }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        {{-- Bukti Kelengkapan --}}
                        <div class="section-heading">Bukti Kelengkapan</div>

                        {{-- Google Drive link dari asesi --}}
                        @php $gdriveLink = $apl->buktiKelengkapan->whereNotNull('gdrive_file_url')->first()?->gdrive_file_url; @endphp
                        @if($gdriveLink)
                        <div class="alert alert-info d-flex align-items-center gap-3 mb-3 py-2">
                            <i class="bi bi-google fs-4 flex-shrink-0"></i>
                            <div class="flex-grow-1 small overflow-hidden">
                                <strong>Google Drive dari Asesi:</strong><br>
                                <a href="{{ $gdriveLink }}" target="_blank" class="text-break small">{{ $gdriveLink }}</a>
                            </div>
                            <a href="{{ $gdriveLink }}" target="_blank" class="btn btn-sm btn-primary flex-shrink-0">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Buka
                            </a>
                        </div>
                        @else
                        <div class="alert alert-secondary py-2 mb-3 small">
                            <i class="bi bi-exclamation-circle me-2"></i>Asesi belum mengisi link Google Drive.
                        </div>
                        @endif

                        {{-- Progress bukti --}}
                        @php
                            $totalBukti = $apl->buktiKelengkapan->count();
                            $isiCount   = $apl->buktiKelengkapan->where('status', '!=', 'Tidak Ada')->count();
                            $okCount    = $apl->buktiKelengkapan->where('status', 'Ada Memenuhi Syarat')->count();
                            $pctIsi     = $totalBukti > 0 ? round($isiCount / $totalBukti * 100) : 0;
                        @endphp
                        @if($totalBukti > 0)
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar bg-{{ $isiCount === $totalBukti ? 'success' : 'warning' }}"
                                    style="width:{{ $pctIsi }}%;"></div>
                            </div>
                            <span class="small text-muted text-nowrap">{{ $isiCount }}/{{ $totalBukti }} diperiksa</span>
                            <span class="badge bg-success" id="bukti-badge-ok">{{ $okCount }} OK</span>
                            <span class="badge bg-secondary" id="bukti-badge-belum">{{ $totalBukti - $isiCount }} Belum</span>
                        </div>
                        @endif

                        {{-- List bukti per kategori --}}
                        @foreach(['persyaratan_dasar' => 'Persyaratan Dasar', 'administratif' => 'Administratif'] as $kat => $katLabel)
                        @php $items = $apl->buktiKelengkapan->where('kategori', $kat); @endphp
                        @if($items->isNotEmpty())
                        <div class="fw-semibold small text-muted mb-2 mt-3">
                            <i class="bi bi-folder2-open me-1"></i>{{ $katLabel }}
                        </div>
                        @foreach($items as $bukti)
                        @php
                            $bc = match($bukti->status) {
                                'Ada Memenuhi Syarat'       => 'ok',
                                'Ada Tidak Memenuhi Syarat' => 'warn',
                                default                     => '',
                            };
                            $badgeColor = match($bukti->status) {
                                'Ada Memenuhi Syarat'       => 'success',
                                'Ada Tidak Memenuhi Syarat' => 'warning',
                                default                     => 'secondary',
                            };
                        @endphp
                        <div class="card bukti-card {{ $bc }} mb-2 shadow-sm">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="small flex-grow-1">
                                        <span class="fw-semibold">{{ $bukti->nama_dokumen }}</span>
                                        @if($bukti->catatan)
                                        <div class="text-muted" style="font-size:.78rem;">
                                            <i class="bi bi-chat-left-dots me-1"></i>{{ $bukti->catatan }}
                                        </div>
                                        @endif
                                    </div>
                                    {{-- Editable hanya jika APL masih submitted, read-only jika sudah verified --}}
                                    @if($apl->status === 'submitted')
                                    <select class="form-select form-select-sm flex-shrink-0" style="min-width:200px;"
                                        data-bukti-id="{{ $bukti->id }}"
                                        onchange="updateStatusBuktiInline({{ $bukti->id }}, this.value, this)">
                                        <option value="Tidak Ada"                {{ $bukti->status === 'Tidak Ada'                ? 'selected' : '' }}>Tidak Ada</option>
                                        <option value="Ada Memenuhi Syarat"      {{ $bukti->status === 'Ada Memenuhi Syarat'      ? 'selected' : '' }}>Ada — Memenuhi Syarat</option>
                                        <option value="Ada Tidak Memenuhi Syarat"{{ $bukti->status === 'Ada Tidak Memenuhi Syarat'? 'selected' : '' }}>Ada — Tidak Memenuhi</option>
                                    </select>
                                    @else
                                    <span class="badge bg-{{ $badgeColor }} text-nowrap flex-shrink-0"
                                        id="badge-bukti-{{ $bukti->id }}">
                                        {{ $bukti->status }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        @endforeach

                        {{-- TTD Pemohon --}}
                        @if($apl->ttd_pemohon)
                        <div class="section-heading mt-4">Tanda Tangan Pemohon</div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="{{ $apl->ttd_pemohon_image }}" class="ttd-thumb" alt="TTD Pemohon">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $apl->nama_ttd_pemohon }}</div>
                                {{ $apl->tanggal_ttd_pemohon ? \Carbon\Carbon::parse($apl->tanggal_ttd_pemohon)->translatedFormat('d M Y') : '-' }}
                            </div>
                        </div>
                        @endif

                        {{-- TTD Admin --}}
                        @if($apl->ttd_admin)
                        <div class="section-heading">Tanda Tangan Admin LSP</div>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $apl->ttd_admin_image }}" class="ttd-thumb" style="border-color:#bbf7d0;" alt="TTD Admin">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $apl->nama_ttd_admin }}</div>
                                {{ $apl->tanggal_ttd_admin ? \Carbon\Carbon::parse($apl->tanggal_ttd_admin)->translatedFormat('d M Y') : '-' }}
                                @if($apl->verified_at)
                                <div class="mt-1">
                                    <i class="bi bi-clock me-1"></i>{{ $apl->verified_at->translatedFormat('d M Y H:i') }}
                                    @if($apl->verifier) — <strong>{{ $apl->verifier->name }}</strong>@endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 3 — APL-02 (read-only dari doc-6)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-apl02">
                @if(!$asesmen->apldua)
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                    <p class="mt-2">APL-02 belum diisi oleh asesi.</p>
                </div>
                @else
                @php $apl02 = $asesmen->apldua; $prog = $apl02->progress; @endphp

                {{-- Status bar --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded status-bar-default">
                    <span class="badge bg-{{ $apl02->status_badge }} fs-6">{{ $apl02->status_label }}</span>
                    <div class="small text-muted">
                        Progress: <strong>{{ $prog['answered'] }}/{{ $prog['total'] }}</strong> elemen dijawab
                        (<span class="text-success">K: {{ $prog['k'] }}</span> /
                        <span class="text-danger">BK: {{ $prog['bk'] }}</span>)
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        @if(in_array($apl02->status, ['verified', 'approved']))
                        <a href="{{ route('admin.apl02.pdf', [$apl02, 'preview' => 1]) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-pdf me-1"></i>Preview PDF
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Progress summary --}}
                <div class="row g-3 mb-4 text-center">
                    <div class="col-4">
                        <div class="bg-light rounded p-3">
                            <div class="fw-bold fs-4">{{ $prog['total'] }}</div>
                            <div class="small text-muted">Total Elemen</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <div class="fw-bold fs-4 text-success">{{ $prog['k'] }}</div>
                            <div class="small text-muted">Kompeten (K)</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-danger bg-opacity-10 rounded p-3">
                            <div class="fw-bold fs-4 text-danger">{{ $prog['bk'] }}</div>
                            <div class="small text-muted">Blm Kompeten (BK)</div>
                        </div>
                    </div>
                </div>

                {{-- Jawaban per unit (accordion) --}}
                @foreach($asesmen->skema->unitKompetensis as $unitIdx => $unit)
                <div class="unit-card" id="apl02-unit-{{ $unit->id }}">
                    <div class="unit-header" onclick="toggleUnit('apl02-{{ $unit->id }}')">
                        <i class="bi bi-chevron-right" id="arrow-apl02-{{ $unit->id }}"
                           style="transition:transform .2s; font-size:1rem; color:#94a3b8;"></i>
                        <span class="badge bg-secondary me-1">{{ $unitIdx + 1 }}</span>
                        <div class="flex-grow-1">
                            <div class="small text-muted">{{ $unit->kode_unit }}</div>
                            <div class="fw-semibold" style="font-size:.9rem;">{{ $unit->judul_unit }}</div>
                        </div>
                    </div>
                    <div id="body-apl02-{{ $unit->id }}" style="{{ $unitIdx === 0 ? '' : 'display:none;' }}">
                        @foreach($unit->elemens as $elemen)
                        @php $jaw = $jawabanMap[$elemen->id] ?? null; @endphp
                        <div class="elemen-row {{ $jaw?->jawaban ? 'answered-'.$jaw->jawaban : 'unanswered' }} d-flex align-items-start gap-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small mb-1">{{ $elemen->judul }}</div>
                                @if($elemen->kuks->isNotEmpty())
                                <ul class="mb-1" style="padding-left:14px;">
                                    @foreach($elemen->kuks as $kuk)
                                    <li style="font-size:.75rem; color:#6b7280;">{{ $kuk->kode }} — {{ $kuk->deskripsi }}</li>
                                    @endforeach
                                </ul>
                                @endif
                                @if($jaw?->bukti)
                                <div class="text-muted mt-1" style="font-size:.78rem;">
                                    <i class="bi bi-chat-left-dots me-1"></i>{{ $jaw->bukti }}
                                </div>
                                @endif
                            </div>
                            <div class="flex-shrink-0 text-end" style="min-width:100px;">
                                @if($jaw?->jawaban)
                                <span class="ro-badge ro-badge-{{ $jaw->jawaban }}">
                                    {{ $jaw->jawaban === 'K' ? 'Kompeten' : 'Blm Kompeten' }}
                                </span>
                                @else
                                <span class="badge bg-secondary">Belum diisi</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                {{-- TTD --}}
                @if($apl02->ttd_asesi || $apl02->ttd_asesor)
                <div class="row g-3 mt-3">
                    @if($apl02->ttd_asesi)
                    <div class="col-md-4">
                        <div class="section-heading">TTD Asesi</div>
                        <img src="{{ $apl02->ttd_asesi_image }}" class="ttd-thumb" alt="TTD Asesi">
                        <div class="text-muted small mt-1">{{ $apl02->nama_ttd_asesi }}</div>
                    </div>
                    @endif
                    @if($apl02->ttd_asesor)
                    <div class="col-md-4">
                        <div class="section-heading">TTD Asesor</div>
                        <img src="{{ $apl02->ttd_asesor_image }}" class="ttd-thumb" alt="TTD Asesor">
                        <div class="text-muted small mt-1">{{ $apl02->nama_ttd_asesor }}</div>
                        @if($apl02->rekomendasi_asesor)
                        <span class="badge bg-{{ $apl02->rekomendasi_asesor === 'lanjut' ? 'success' : 'danger' }} mt-1 d-block" style="width:fit-content;">
                            {{ $apl02->rekomendasi_asesor === 'lanjut' ? 'Lanjut Asesmen' : 'Tidak Lanjut' }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
                @endif
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 4 — FR.AK.01 (read-only dari doc-7)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-frak01">
                @include('admin.asesmen.partials._admin_frak01_tab')
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 5 — FR.AK.04 / BANDING (read-only dari doc-8)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-frak04">
                @if(!$asesmen->frak04 || $asesmen->frak04->status === 'draft')
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-megaphone fs-1 opacity-25"></i>
                    <p class="mt-2">Tidak ada banding yang diajukan.</p>
                </div>
                @else
                @php $ak04 = $asesmen->frak04; @endphp

                {{-- Status bar --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded"
                     style="background:#fffbeb; border:1px solid #fde68a;">
                    <span class="badge bg-{{ $ak04->status_badge }} fs-6">{{ $ak04->status_label }}</span>
                    @if($ak04->submitted_at)
                    <span class="small text-muted">Diajukan: {{ $ak04->submitted_at->translatedFormat('d M Y H:i') }}</span>
                    @endif
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('admin.frak04.pdf', [$ak04, 'preview' => 1]) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-pdf me-1"></i>Preview PDF
                        </a>
                        <a href="{{ route('admin.frak04.pdf', $ak04) }}"
                           class="btn btn-sm btn-success">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="section-heading">Detail Banding</div>
                        <div class="info-row"><span class="info-label">Asesi</span><span class="info-value">{{ $ak04->nama_asesi }}</span></div>
                        <div class="info-row"><span class="info-label">Asesor</span><span class="info-value">{{ $ak04->nama_asesor }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Asesmen</span><span class="info-value">{{ $ak04->tanggal_asesmen }}</span></div>
                        <div class="info-row"><span class="info-label">Skema Sertifikasi</span><span class="info-value">{{ $ak04->skema_sertifikasi ?? $asesmen->skema?->name ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">No. Skema</span><span class="info-value font-monospace small">{{ $ak04->no_skema_sertifikasi ?? $asesmen->skema?->nomor_skema ?? '-' }}</span></div>

                        <div class="section-heading mt-4">Pernyataan</div>
                        @foreach([
                            ['label' => 'Proses banding sudah dijelaskan', 'val' => $ak04->proses_banding_dijelaskan],
                            ['label' => 'Sudah diskusi dengan asesor',      'val' => $ak04->sudah_diskusi_dengan_asesor],
                            ['label' => 'Melibatkan pihak lain',            'val' => $ak04->melibatkan_orang_lain],
                        ] as $item)
                        <div class="pertanyaan-card {{ $item['val'] !== null ? 'answered' : '' }}">
                            <div class="small fw-semibold mb-1">{{ $item['label'] }}</div>
                            <span class="badge bg-{{ $item['val'] ? 'success' : 'secondary' }} px-3">
                                {{ $item['val'] ? 'YA' : 'TIDAK' }}
                            </span>
                        </div>
                        @endforeach

                        <div class="section-heading mt-3">Alasan Banding</div>
                        <div class="p-3 bg-light rounded small" style="white-space:pre-wrap;">{{ $ak04->alasan_banding }}</div>
                    </div>

                    <div class="col-md-5">
                        @if($ak04->ttd_asesi)
                        <div class="section-heading">TTD Pengaju Banding</div>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $ak04->ttd_asesi_image }}" class="ttd-thumb">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $ak04->nama_ttd_asesi }}</div>
                                {{ $ak04->tanggal_ttd_asesi?->translatedFormat('d M Y H:i') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

        </div>{{-- end tab-content --}}
    </div>{{-- end card-body --}}
</div>{{-- end card --}}


{{-- ══ MODAL VERIFIKASI APL-01 ══ --}}
<div class="modal fade" id="modalVerifikasiApl" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-pen me-2"></i>Verifikasi APL-01</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-4">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Dengan menandatangani, Anda menyatakan APL-01 ini telah diperiksa dan semua bukti kelengkapan telah diverifikasi.
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Nama Lengkap Admin <span class="text-danger">*</span></label>
                    <input type="text" id="admin-nama-input" class="form-control form-control-lg"
                        value="{{ auth()->user()->name }}" readonly>
                    <div class="form-text">Nama ini akan tercetak di dokumen PDF APL-01.</div>
                </div>
                @include('partials._signature_pad', [
                    'padId'    => 'admin-apl',
                    'padLabel' => 'Tanda Tangan Admin LSP',
                    'padHeight' => 180,
                    'savedSig' => auth()->user()->signature_image,
                ])
                <div class="card bg-light border-0 mt-3">
                    <div class="card-body py-2 small">
                        <strong>Pemohon:</strong> {{ $asesmen->full_name }} |
                        <strong>Skema:</strong> {{ $asesmen->skema?->name ?? '-' }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4" id="btn-simpan-verifikasi-apl"
                    onclick="submitVerifikasiApl()">
                    <i class="bi bi-check-circle me-1"></i>Verifikasi & Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL REJECT APL-01 ══ --}}
<div class="modal fade" id="modalRejectApl01" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-x me-2"></i>Kembalikan APL-01</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <small>APL-01 akan dikembalikan ke status <strong>returned</strong>. Asesi dapat mengedit dan submit ulang.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alasan Pengembalian <span class="text-danger">*</span></label>
                    <textarea id="apl01-rejection-notes" class="form-control" rows="4"
                        placeholder="Jelaskan apa yang perlu diperbaiki..." maxlength="1000"></textarea>
                    <div class="form-text">Min. 10 karakter.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-reject-apl01"
                    onclick="submitRejectApl01()">
                    <i class="bi bi-send me-1"></i>Kembalikan APL-01
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

    // ── APL-01 Verifikasi ───────────────────────────────────────
const APL_ID = '{{ $asesmen->aplsatu?->id }}';
const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
async function updateStatusBuktiInline(buktiId, status, selectEl) {
    if (!selectEl.dataset.semula) selectEl.dataset.semula = selectEl.value;
    const semula = selectEl.dataset.semula;

    const { isConfirmed, value: catatan } = await Swal.fire({
        title: 'Update Status Bukti',
        html: `<p class="text-start mb-2">Status baru: <span class="badge bg-primary">${status}</span></p>
               <div class="text-start">
                   <label class="form-label small">Catatan (opsional):</label>
                   <textarea class="form-control form-control-sm" id="swal-catatan" rows="2"
                       placeholder="Tambahkan catatan..."></textarea>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Batal',
        preConfirm: () => document.getElementById('swal-catatan')?.value?.trim() ?? '',
    });

    if (!isConfirmed) { selectEl.value = semula; return; }

    try {
        const res  = await fetch(`/admin/apl01-bukti/${buktiId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                Accept: 'application/json',
            },
            body: JSON.stringify({ status, catatan }),
        });
        const data = await res.json();

        if (data.success) {
            // Update border warna card
            const card = selectEl.closest('.bukti-card');
            if (card) {
                card.classList.remove('ok', 'warn');
                if (status === 'Ada Memenuhi Syarat')       card.classList.add('ok');
                if (status === 'Ada Tidak Memenuhi Syarat') card.classList.add('warn');
            }
            selectEl.dataset.semula = status;
            
            // ── Update progress counter ──────────────────────────
            updateBuktiProgress();

            Swal.fire({ icon: 'success', title: 'Tersimpan!', timer: 1200, showConfirmButton: false });
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
            selectEl.value = semula;
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        selectEl.value = semula;
    }
}
function bukaModalVerifikasiApl() {
    const modalEl = document.getElementById('modalVerifikasiApl');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    modalEl.addEventListener('shown.bs.modal', () => {
        SigPadManager.init('admin-apl', @json(auth()->user()->signature_image));
    }, { once: true });
}

async function submitVerifikasiApl() {
    const namaAdmin = document.getElementById('admin-nama-input')?.value?.trim() ?? '';
    if (!namaAdmin) { Swal.fire('Perhatian', 'Nama admin diperlukan.', 'warning'); return; }
    if (SigPadManager.isEmpty('admin-apl')) { Swal.fire('Perhatian', 'Tanda tangan diperlukan.', 'warning'); return; }

    const btn = document.getElementById('btn-simpan-verifikasi-apl');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    try {
        const signature = await SigPadManager.prepareAndGet('admin-apl');
        const res  = await fetch(`/admin/apl01/${APL_ID}/verify`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ signature, nama_admin: namaAdmin }),
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalVerifikasiApl'))?.hide();
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Verifikasi & Simpan';
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Verifikasi & Simpan';
    }
}

function openRejectApl01Modal() {
    document.getElementById('apl01-rejection-notes').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRejectApl01')).show();
}

async function submitRejectApl01() {
    const notes = document.getElementById('apl01-rejection-notes').value.trim();
    if (notes.length < 10) { Swal.fire('Perhatian', 'Catatan minimal 10 karakter.', 'warning'); return; }

    const btn = document.getElementById('btn-confirm-reject-apl01');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    try {
        const res  = await fetch(`/admin/apl01/${APL_ID}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ catatan: notes }),
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalRejectApl01'))?.hide();
            await Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1800, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i>Kembalikan APL-01';
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i>Kembalikan APL-01';
    }
}

// ── Update progress bukti kelengkapan (real-time) ───────────
function updateBuktiProgress() {
    const selects = document.querySelectorAll('[data-bukti-id]');
    const total   = selects.length;
    if (!total) return;

    let isiCount = 0;
    let okCount  = 0;

    selects.forEach(sel => {
        const val = sel.value;
        if (val !== 'Tidak Ada')             isiCount++;
        if (val === 'Ada Memenuhi Syarat')   okCount++;
    });

    const belum = total - isiCount;
    const pct   = Math.round(isiCount / total * 100);

    // Progress bar
    const bar = document.querySelector('.progress-bar');
    if (bar) {
        bar.style.width   = pct + '%';
        bar.className     = 'progress-bar bg-' + (isiCount === total ? 'success' : 'warning');
    }

    // "X/Y diperiksa"
    const counterEl = document.querySelector('.small.text-muted.text-nowrap');
    if (counterEl) counterEl.textContent = `${isiCount}/${total} diperiksa`;

    // Badge OK — pakai ID supaya tidak salah ambil badge lain
    const badgeOk = document.querySelector('#bukti-badge-ok');
    if (badgeOk) badgeOk.textContent = `${okCount} OK`;

    const badgeBelum = document.querySelector('#bukti-badge-belum');
    if (badgeBelum) badgeBelum.textContent = `${belum} Belum`;
}

// ── Toggle accordion unit (APL-02 & AK.01) ─────────────────
function toggleUnit(key) {
    const body  = document.getElementById(`body-${key}`);
    const arrow = document.getElementById(`arrow-${key}`);
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (arrow) arrow.style.transform = open ? '' : 'rotate(90deg)';
}

// ── Auto-buka tab via ?tab=... ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const tabParam = new URLSearchParams(window.location.search).get('tab');
    if (tabParam) {
        const tabEl = document.querySelector(`[data-bs-target="#tab-${tabParam}"]`);
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
});


async function gantiEmailAsesi(asesmenId) {
    const email = document.getElementById('inputEmailBaru').value.trim();
    if (!email) return;

    const result = await Swal.fire({
        title: 'Ganti Email?',
        html: `Email akan diganti ke <strong>${email}</strong>.<br>
               <small class="text-muted">Status verifikasi akan direset.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Ganti',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#f59e0b',
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/asesi/${asesmenId}/update-email`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email }),
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
            });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
    }
}

async function resetPasswordAsesi(asesmenId) {
    const result = await Swal.fire({
        title: 'Reset Password?',
        html: `Password asesi akan direset ke <code>password123</code>.<br>
               <small class="text-muted">Asesi akan diminta ganti password saat login berikutnya.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/asesi/${asesmenId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
    }
}
</script>
@endpush